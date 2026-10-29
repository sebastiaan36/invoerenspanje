<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class WerkgebiedConfigTest extends TestCase
{
    /**
     * @return array{gebied: list<array{0: float, 1: float}>, plaatsen: list<array{naam: string, lat: float, lng: float}>}
     */
    private function config(): array
    {
        return require dirname(__DIR__, 2).'/config/werkgebied.php';
    }

    /**
     * Ray casting: count how many polygon edges a ray to the east crosses.
     *
     * @param  list<array{0: float, 1: float}>  $polygon
     */
    private function contains(array $polygon, float $lat, float $lng): bool
    {
        $inside = false;
        $count = count($polygon);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            [$latI, $lngI] = $polygon[$i];
            [$latJ, $lngJ] = $polygon[$j];

            if (($latI > $lat) !== ($latJ > $lat)
                && $lng < ($lngJ - $lngI) * ($lat - $latI) / ($latJ - $latI) + $lngI) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    public function test_every_listed_place_lies_inside_the_work_area(): void
    {
        $config = $this->config();

        foreach ($config['plaatsen'] as $plaats) {
            $this->assertTrue(
                $this->contains($config['gebied'], $plaats['lat'], $plaats['lng']),
                "{$plaats['naam']} ligt buiten het werkgebied.",
            );
        }
    }

    public function test_the_coastal_towns_and_the_sea_border_are_covered(): void
    {
        $gebied = $this->config()['gebied'];

        // The line follows the coast a little out to sea, so towns on the
        // beach fall inside and the open sea further out does not.
        foreach (['Nerja' => [36.7457, -3.8760], 'Torremolinos' => [36.6203, -4.4998], 'Marbella' => [36.5099, -4.8858], 'Estepona' => [36.4256, -5.1450], 'Manilva' => [36.3768, -5.2500]] as $naam => [$lat, $lng]) {
            $this->assertTrue($this->contains($gebied, $lat, $lng), "{$naam} ligt buiten het werkgebied.");
        }

        $this->assertFalse($this->contains($gebied, 36.45, -4.60), 'Open zee voor Fuengirola hoort buiten het werkgebied te vallen.');
    }
}
