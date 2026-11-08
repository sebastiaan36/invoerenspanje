<?php

declare(strict_types=1);

namespace App\Services\SpainImport;

/**
 * Resolves the Spanish fixed costs from config to the amounts a customer pays.
 *
 * A cost is configured either as a final amount (`amount_eur`, e.g. DGT fees
 * that carry no VAT) or as an amount excluding VAT plus a rate
 * (`amount_excl_vat_eur` + `vat_pct`, e.g. the gestoría fee + 21% IVA).
 *
 * Items flagged `optional` (e.g. temporary plates) are listed but never
 * counted in a total.
 */
final class SpainFixedCosts
{
    /**
     * @param  list<array{key: string, label: string, amount_eur?: float|int, amount_excl_vat_eur?: float|int, vat_pct?: float|int, optional?: bool}>  $items
     * @return list<array{key: string, label: string, amount_eur: float, optional: bool}>
     */
    public static function resolve(array $items): array
    {
        return array_map(fn (array $item): array => [
            'key' => $item['key'],
            'label' => $item['label'],
            'amount_eur' => self::amountInclVat($item),
            'optional' => (bool) ($item['optional'] ?? false),
        ], $items);
    }

    /**
     * @return list<array{key: string, label: string, amount_eur: float, optional: bool}>
     */
    public static function fromConfig(): array
    {
        return self::resolve(config('spain_import.fixed_costs'));
    }

    /**
     * Sum of the non-optional fixed costs: what every customer pays.
     *
     * @param  list<array{key: string, label: string, amount_eur: float, optional: bool}>|null  $resolved  defaults to the configured costs
     */
    public static function includedTotal(?array $resolved = null): float
    {
        $resolved ??= self::fromConfig();

        $included = array_filter($resolved, fn (array $cost): bool => ! $cost['optional']);

        return round(array_sum(array_column($included, 'amount_eur')), 2);
    }

    /**
     * @param  array{amount_eur?: float|int, amount_excl_vat_eur?: float|int, vat_pct?: float|int}  $item
     */
    private static function amountInclVat(array $item): float
    {
        if (! isset($item['amount_excl_vat_eur'])) {
            return (float) $item['amount_eur'];
        }

        // Work in cents so 181,50 × 1,21 = 219,615 rounds to 219,62 (not 219,61 from float noise).
        return round($item['amount_excl_vat_eur'] * (100 + ($item['vat_pct'] ?? 0))) / 100;
    }
}
