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

        $this->assertSame(102.85, $costs['ficha_tecnica']);
        $this->assertSame(219.62, $costs['honorarios']);
        $this->assertSame(181.5, $costs['matriculas_temporales']);
    }

    public function test_costs_without_vat_keep_their_amount(): void
    {
        $costs = collect(SpainFixedCosts::fromConfig())->pluck('amount_eur', 'key');

        $this->assertSame(125.0, $costs['tasas']);
        $this->assertSame(155.0, $costs['itv_keuring']);
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

        // 125 + 102,85 + 219,62 + 181,50 + 155 = 783,97 (IEDMT 0% onder 120 g/km).
        $this->assertSame(783.97, $result->fixedCostsTotalEur);
        $this->assertSame(783.97, $result->totalEur);
    }
}
