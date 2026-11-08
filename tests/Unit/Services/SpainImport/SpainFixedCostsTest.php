<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SpainImport;

use App\Services\SpainImport\Dto\SpainImportInput;
use App\Services\SpainImport\SpainFixedCosts;
use App\Services\SpainImport\SpainImportCalculator;
use Carbon\CarbonImmutable;
use Tests\TestCase;

final class SpainFixedCostsTest extends TestCase
{
    public function test_costs_excluding_vat_get_21_percent_iva_added(): void
    {
        $costs = collect(SpainFixedCosts::fromConfig())->pluck('amount_eur', 'key');

        $this->assertSame(219.62, $costs['honorarios']);
        $this->assertSame(181.5, $costs['matriculas_temporales']);
    }

    public function test_costs_without_vat_keep_their_amount(): void
    {
        $costs = collect(SpainFixedCosts::fromConfig())->pluck('amount_eur', 'key');

        $this->assertSame(125.0, $costs['tasas']);
        $this->assertSame(155.0, $costs['itv_keuring']);
        $this->assertSame(103.0, $costs['ficha_tecnica']);
    }

    public function test_definitive_plates_are_not_a_separate_cost(): void
    {
        $this->assertNotContains('placas_definitivas', array_column(SpainFixedCosts::fromConfig(), 'key'));
    }

    public function test_the_quote_adds_up_the_amounts_including_vat(): void
    {
        $input = new SpainImportInput(
            datumEersteToelating: CarbonImmutable::create(2020, 1, 1),
            co2: 100,
            catalogusprijsEur: 30000,
            voertuigsoort: 'Personenauto',
        );

        $result = (new SpainImportCalculator(config('spain_import')))->calculate($input, CarbonImmutable::create(2026, 1, 1));

        // 125 + 103 + 219,62 + 155 = 602,62 (IEDMT 0% onder 120 g/km); de optionele
        // tijdelijke kentekenplaten (€ 181,50) tellen niet mee.
        $this->assertSame(602.62, $result->fixedCostsTotalEur);
        $this->assertSame(602.62, $result->totalEur);
    }

    public function test_optional_costs_are_listed_but_not_counted(): void
    {
        $input = new SpainImportInput(
            datumEersteToelating: CarbonImmutable::create(2020, 1, 1),
            co2: 100,
            catalogusprijsEur: 30000,
            voertuigsoort: 'Personenauto',
        );

        $result = (new SpainImportCalculator(config('spain_import')))->calculate($input, CarbonImmutable::create(2026, 1, 1));
        $plates = collect($result->fixedCosts)->firstWhere('key', 'matriculas_temporales');

        $this->assertNotNull($plates);
        $this->assertTrue($plates->optional);
        $this->assertSame(181.5, $plates->amountEur);
        $this->assertFalse(collect($result->fixedCosts)->firstWhere('key', 'honorarios')->optional);
    }

    public function test_the_included_total_skips_optional_items(): void
    {
        $this->assertSame(602.62, SpainFixedCosts::includedTotal());

        $this->assertSame(10.0, SpainFixedCosts::includedTotal(SpainFixedCosts::resolve([
            ['key' => 'a', 'label' => 'A', 'amount_eur' => 10],
            ['key' => 'b', 'label' => 'B', 'amount_eur' => 99, 'optional' => true],
        ])));
    }
}
