<?php

declare(strict_types=1);

namespace Tests\Feature\Hacienda;

use App\Models\HaciendaVehiclePrice;
use App\Services\Hacienda\Dto\HaciendaMatch;
use App\Services\Hacienda\HaciendaVehicleMatcher;
use App\Services\Rdw\Dto\FuelData;
use App\Services\Rdw\Dto\VehicleData;
use App\Services\Rdw\Dto\VehicleLookupResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HaciendaVehicleMatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_takes_the_median_of_the_trims_with_the_same_engine(): void
    {
        $this->price('VOLKSWAGEN', 'T-Roc 1.5 TSI EVO Advance 6V', 2018, 2022, 1498, 'G', 110, 20900);
        $this->price('VOLKSWAGEN', 'T-Roc 1.5 TSI EVO Sport 6V', 2018, 2022, 1498, 'G', 110, 22400);
        $this->price('VOLKSWAGEN', 'T-Roc 1.5 TSI EVO Sport Aut. 7V', 2018, 2022, 1498, 'G', 110, 23500);
        $this->price('VOLKSWAGEN', 'T-Roc 2.0 TSI R 4Motion', 2018, 2022, 1984, 'G', 221, 45000);
        $this->price('VOLKSWAGEN', 'T-Roc 2.0 TDI Sport', 2018, 2022, 1968, 'D', 110, 30000);

        $match = $this->matcher()->match($this->lookup('VOLKSWAGEN', 'T-ROC', '20210510', 1498, 'Benzine', '110.00'));

        $this->assertNotNull($match);
        $this->assertSame(22400, $match->newValueEur);
        $this->assertSame(3, $match->candidateCount);
        $this->assertSame(HaciendaMatch::CONFIDENCE_HIGH, $match->confidence);
    }

    public function test_spacing_in_the_hacienda_model_name_does_not_matter(): void
    {
        $this->price('VOLVO', 'V 50 2.0 Momentum (2004)', 2004, 2012, 1999, 'G', 107, 22400);

        $match = $this->matcher()->match($this->lookup('VOLVO', 'V50', '20060830', 1999, 'Benzine', '107.00'));

        $this->assertSame(22400, $match?->newValueEur);
    }

    public function test_a_model_family_does_not_match_a_longer_number(): void
    {
        $this->price('AUDI', 'A35 Sportback', 2019, null, 1984, 'G', 228, 60000);

        $match = $this->matcher()->match($this->lookup('AUDI', 'A3 SPORTBACK', '20200101', 1498, 'Benzine', '110.00'));

        $this->assertNull($match);
    }

    public function test_the_rdw_brand_name_is_mapped_to_the_hacienda_brand(): void
    {
        $this->price('MERCEDES', 'GLA 180 X156', 2014, 2019, 1595, 'G', 90, 28400);

        $match = $this->matcher()->match($this->lookup('MERCEDES-BENZ', 'GLA 180', '20190301', 1595, 'Benzine', '90.00'));

        $this->assertSame(28400, $match?->newValueEur);
    }

    public function test_the_distinctive_part_of_the_rdw_name_selects_the_right_version(): void
    {
        $this->price('BMW', 'X3 xDrive20i xLine (G01 LCI)', 2021, null, 1998, 'G', 135, 49600);
        $this->price('BMW', 'X3 xDrive30i xLine (G01 LCI)', 2021, null, 1998, 'G', 180, 59900);

        $match = $this->matcher()->match($this->lookup('BMW', 'X3 XDRIVE20I', '20230301', 1998, 'Benzine', '120.00'));

        $this->assertSame(49600, $match?->newValueEur);
    }

    public function test_a_plug_in_hybrid_prefers_phev_rows_and_falls_back_to_its_combustion_fuel(): void
    {
        $this->price('VOLVO', 'V 90 T8 Twin Engine AWD Momentum Aut.', 2017, 2020, 1969, 'G', 223, 63700);

        $lookup = $this->lookup('VOLVO', 'V90 T8 TWIN ENGINE', '20190101', 1969, 'Benzine', '223.00', isPluginHybrid: true);
        $this->assertSame(63700, $this->matcher()->match($lookup)?->newValueEur);

        $this->price('VOLVO', 'V90 T8 Recharge Plus', 2017, 2020, 1969, 'PHEV', 288, 70000);
        $this->assertSame(70000, $this->matcher()->match($lookup)?->newValueEur);
    }

    public function test_a_model_code_later_in_the_hacienda_name_is_the_same_model(): void
    {
        $this->price('BMW', 'Serie 2 Active Tourer 220i (U06)', 2022, null, 1499, 'GyE', 125, 36200);
        $this->price('BMW', 'X1 sDrive20i (U11)', 2022, null, 1499, 'GyE', 125, 41000);

        $match = $this->matcher()->match($this->lookup('BMW', '220I', '20230101', 1499, 'Benzine', '125.00'));

        $this->assertSame(36200, $match?->newValueEur);
    }

    public function test_a_model_missing_from_the_table_does_not_borrow_another_model_with_the_same_engine(): void
    {
        // KSG97T: Kia K4 (2026) is not in the 2026 table; the Stonic has the same 1.0 T-GDi.
        $this->price('KIA', 'Stonic 1.0 T-GDiMHEV GT Line DCT (2021)', 2021, null, 998, 'GyE', 85, 21700);

        $this->assertNull($this->matcher()->match($this->lookup('KIA', 'K4', '20260101', 998, 'Benzine', '85.00')));
    }

    public function test_brand_words_before_the_model_name_are_ignored(): void
    {
        // RDW: "ALFA GIULIETTA"; Hacienda lists both "GIULIETTA …" and "ALFA 159 …".
        $this->price('ALFA ROMEO', 'ALFA 159 1.8 TB Elegante', 2009, 2012, 1742, 'G', 147, 24650);
        $this->price('ALFA ROMEO', 'GIULIETTA 1.4 TB Distinctive', 2010, 2016, 1368, 'G', 88, 21000);

        $giulietta = $this->matcher()->match($this->lookup('ALFA ROMEO', 'ALFA GIULIETTA', '20120101', 1368, 'Benzine', '88.00'));
        $this->assertSame('GIULIETTA 1.4 TB Distinctive', $giulietta?->model);

        $alfa159 = $this->matcher()->match($this->lookup('ALFA ROMEO', '159', '20100101', 1742, 'Benzine', '147.00'));
        $this->assertSame('ALFA 159 1.8 TB Elegante', $alfa159?->model);
    }

    public function test_a_known_model_never_switches_to_another_model_with_the_same_engine(): void
    {
        // SZ226V: Toyota C-HR 1.2 Turbo (2018). Hacienda only lists the C-HR as hybrid;
        // the Auris has the identical 1.2 Turbo engine but is a different model.
        $this->price('TOYOTA', 'AURIS 120T Active (2016-)', 2016, null, 1197, 'G', 85, 19500);
        $this->price('TOYOTA', 'C-HR 1.8 Hybrid Aut. Active', 2017, 2018, 1798, 'GyE', 90, 25200);
        $this->price('TOYOTA', 'C-HR 1.8 Hybrid Aut. Style Plus', 2018, 2019, 1798, 'GyE', 90, 25600);
        $this->price('TOYOTA', 'C-HR 2.0 Advance', 2019, 2020, 1987, 'GyE', 135, 29000);

        $match = $this->matcher()->match($this->lookup('TOYOTA', 'TOYOTA C-HR', '20180629', 1197, 'Benzine', '85.00'));

        $this->assertNotNull($match);
        $this->assertStringStartsWith('C-HR', $match->model);
        $this->assertSame(25400, $match->newValueEur);
        $this->assertSame(HaciendaMatch::CONFIDENCE_LOW, $match->confidence);
    }

    public function test_a_known_model_outside_its_listed_period_takes_the_nearest_period(): void
    {
        $this->price('VOLKSWAGEN', 'GOLF 1.4 TSI', 2008, 2012, 1390, 'G', 90, 20000);
        $this->price('VOLKSWAGEN', 'GOLF 1.5 TSI', 2019, null, 1498, 'G', 110, 26000);
        $this->price('VOLKSWAGEN', 'POLO 1.4 TSI', 2014, 2016, 1395, 'G', 92, 15000);

        $match = $this->matcher()->match($this->lookup('VOLKSWAGEN', 'GOLF', '20150101', 1395, 'Benzine', '92.00'));

        $this->assertSame('GOLF 1.4 TSI', $match?->model);
    }

    public function test_it_returns_null_when_nothing_fits(): void
    {
        $this->price('VOLKSWAGEN', 'GOLF 1.5 TSI', 2019, null, 1498, 'G', 110, 26000);

        $this->assertNull($this->matcher()->match($this->lookup('SKODA', 'OCTAVIA', '20200101', 1498, 'Benzine', '110.00')));
    }

    private function matcher(): HaciendaVehicleMatcher
    {
        return app(HaciendaVehicleMatcher::class);
    }

    private function price(string $brand, string $model, int $startYear, ?int $endYear, ?int $cc, string $fuel, int $kw, int $priceEur): void
    {
        HaciendaVehiclePrice::query()->create([
            'table_year' => config('spain_import.hacienda.table_year'),
            'brand_key' => HaciendaVehiclePrice::normalizeKey($brand),
            'brand' => $brand,
            'model' => $model,
            'start_year' => $startYear,
            'end_year' => $endYear,
            'cc' => $cc,
            'fuel' => $fuel,
            'kw' => $kw,
            'cv' => null,
            'price_eur' => $priceEur,
        ]);
    }

    private function lookup(
        string $merk,
        string $handelsbenaming,
        string $datumEersteToelating,
        ?int $cc,
        string $brandstof,
        string $kw,
        bool $isPluginHybrid = false,
    ): VehicleLookupResult {
        $vehicle = VehicleData::fromRdwRow([
            'kenteken' => '12ABC3',
            'merk' => $merk,
            'handelsbenaming' => $handelsbenaming,
            'datum_eerste_toelating' => $datumEersteToelating,
            'cilinderinhoud' => $cc === null ? null : (string) $cc,
        ]);
        $fuel = FuelData::fromRdwRow([
            'kenteken' => '12ABC3',
            'brandstof_omschrijving' => $brandstof,
            'nettomaximumvermogen' => $kw,
        ]);

        return new VehicleLookupResult('12ABC3', $vehicle, $fuel, isPluginHybrid: $isPluginHybrid);
    }
}
