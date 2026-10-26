<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Quote;

use App\Services\Quote\VehicleQuoteService;
use App\Services\Rdw\RdwUnavailableException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class VehicleQuoteServiceTest extends TestCase
{
    public function test_an_unknown_kenteken_gives_a_not_found_quote_without_calculations(): void
    {
        Http::fake([
            'opendata.rdw.nl/*' => Http::response([]),
        ]);

        $quote = app(VehicleQuoteService::class)->quote('00-zzz-0');

        $this->assertFalse($quote->found());
        $this->assertSame('00ZZZ0', $quote->kenteken);
        $this->assertNull($quote->bpm);
        $this->assertNull($quote->spainImport);
        $this->assertNull($quote->netEffectEur());
    }

    public function test_an_rdw_outage_is_not_reported_as_an_unknown_kenteken(): void
    {
        Http::fake([
            'opendata.rdw.nl/*' => Http::response('Service Unavailable', 503),
        ]);

        $this->expectException(RdwUnavailableException::class);

        app(VehicleQuoteService::class)->quote('12ABC3');
    }

    public function test_residency_change_exempts_iedmt_and_keeps_the_baseline_for_the_savings(): void
    {
        $this->fakeKnownVehicle(co2: '180');

        $quote = app(VehicleQuoteService::class)->quote('12ABC3', residencyChange: true);

        $this->assertTrue($quote->found());
        $this->assertNotNull($quote->spainImport);
        $this->assertNotNull($quote->spainImportWithoutExemption);
        $this->assertTrue($quote->spainImport->iedmtExempt);
        $this->assertSame(0.0, $quote->spainImport->iedmtEur);
        $this->assertGreaterThan(0.0, $quote->spainImportWithoutExemption->iedmtEur);
        $this->assertSame(
            $quote->spainImportWithoutExemption->iedmtEur,
            $quote->importSnapshot()['iedmt_without_exemption_eur'],
        );
    }

    public function test_without_residency_change_there_is_no_separate_baseline(): void
    {
        $this->fakeKnownVehicle(co2: '180');

        $quote = app(VehicleQuoteService::class)->quote('12ABC3');

        $this->assertNull($quote->spainImportWithoutExemption);
        $this->assertFalse($quote->spainImport?->iedmtExempt);
    }

    public function test_a_known_vehicle_without_co2_gets_no_bpm_amount(): void
    {
        $this->fakeKnownVehicle(co2: null);

        $quote = app(VehicleQuoteService::class)->quote('12ABC3');

        $this->assertTrue($quote->found());
        $this->assertNull($quote->bpm);
        $this->assertNull($quote->bpmSnapshot());
    }

    private function fakeKnownVehicle(?string $co2): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'voertuigsoort' => 'Personenauto',
                'merk' => 'VOLKSWAGEN',
                'handelsbenaming' => 'GOLF',
                'datum_eerste_toelating' => '20190401',
                'catalogusprijs' => '31500',
            ]]),
            'opendata.rdw.nl/resource/8ys7-d773.json*' => Http::response([array_filter([
                'kenteken' => '12ABC3',
                'brandstof_omschrijving' => 'Benzine',
                'co2_uitstoot_gecombineerd' => $co2,
            ])]),
        ]);
    }
}
