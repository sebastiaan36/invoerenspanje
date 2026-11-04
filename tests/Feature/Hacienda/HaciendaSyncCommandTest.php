<?php

declare(strict_types=1);

namespace Tests\Feature\Hacienda;

use App\Models\HaciendaVehiclePrice;
use App\Services\Hacienda\HaciendaPriceImporter;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class HaciendaSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = storage_path('framework/testing/hacienda-'.uniqid());
        $this->app->bind(HaciendaPriceImporter::class, fn ($app) => new HaciendaPriceImporter(
            $app->make(CacheRepository::class),
            $this->directory,
        ));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    public function test_it_imports_the_csv_and_skips_it_when_unchanged(): void
    {
        app(HaciendaPriceImporter::class)->writeCsv(2026, [
            $this->row('MERCEDES', 'GLA 180 X156', 28400),
            $this->row('VOLVO', 'V 50 2.0 Momentum', 22400),
        ]);

        $this->artisan('hacienda:sync')->expectsOutputToContain('2026: Geïmporteerd (2 regels)')->assertSuccessful();

        $this->assertSame(2, HaciendaVehiclePrice::query()->count());
        $this->assertSame('MERCEDES', HaciendaVehiclePrice::query()->where('price_eur', 28400)->value('brand_key'));
        $this->assertNull(HaciendaVehiclePrice::query()->where('price_eur', 22400)->value('end_year'));

        $this->artisan('hacienda:sync')->expectsOutputToContain('2026: Ongewijzigd (2 regels)')->assertSuccessful();
    }

    public function test_a_changed_csv_replaces_the_rows_of_that_year(): void
    {
        $importer = app(HaciendaPriceImporter::class);
        $importer->writeCsv(2026, [$this->row('VOLVO', 'V 50 2.0 Momentum', 22400)]);
        $this->artisan('hacienda:sync')->assertSuccessful();

        $importer->writeCsv(2026, [$this->row('VOLVO', 'V 50 2.0 Momentum', 23000)]);
        $this->artisan('hacienda:sync')->assertSuccessful();

        $this->assertSame([23000], HaciendaVehiclePrice::query()->pluck('price_eur')->all());
    }

    public function test_it_warns_when_there_is_no_csv(): void
    {
        $this->artisan('hacienda:sync')->expectsOutputToContain('Geen CSV-bestanden gevonden.')->assertSuccessful();
    }

    /**
     * @return array{brand: string, model: string, start_year: int, end_year: int|null, cc: int|null, fuel: string, kw: int, cv: int, price_eur: int}
     */
    private function row(string $brand, string $model, int $priceEur): array
    {
        return [
            'brand' => $brand,
            'model' => $model,
            'start_year' => 2004,
            'end_year' => null,
            'cc' => 1999,
            'fuel' => 'G',
            'kw' => 107,
            'cv' => 146,
            'price_eur' => $priceEur,
        ];
    }
}
