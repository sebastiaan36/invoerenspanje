<?php

declare(strict_types=1);

namespace App\Services\Revenue;

use App\Models\Lead;
use App\Services\Packages\ServicePackages;
use Carbon\CarbonInterface;

/**
 * Single source of truth for revenue from won leads, used by the dashboard
 * widgets and the staffel page.
 *
 * - A lead counts in the period in which it was won (`won_at`), not when it came in.
 * - Revenue is the fee actually agreed on the lead's dossier (`dossiers.service_fee_eur`);
 *   only when no dossier fee exists do we fall back to the current package price.
 * - Everything is aggregated in SQL, so no lead models are loaded.
 */
final class WonLeadRevenue
{
    /**
     * Won leads (with a package) and their revenue, won on or after `$from`
     * and, when given, before `$until`.
     *
     * @return array{count: int, revenue: int}
     */
    public static function between(CarbonInterface $from, ?CarbonInterface $until = null): array
    {
        [$packagePriceSql, $packagePriceBindings] = self::packagePriceCase();

        $row = Lead::query()
            ->where('status', Lead::STATUS_WON)
            ->whereNotNull('package_slug')
            ->where('won_at', '>=', $from)
            ->when($until !== null, fn ($query) => $query->where('won_at', '<', $until))
            ->selectRaw(
                'COUNT(*) AS won_count, COALESCE(SUM(COALESCE(('
                .'SELECT MAX(dossiers.service_fee_eur) FROM dossiers WHERE dossiers.lead_id = leads.id'
                .'), '.$packagePriceSql.')), 0) AS revenue',
                $packagePriceBindings,
            )
            ->toBase()
            ->first();

        return [
            'count' => (int) ($row->won_count ?? 0),
            'revenue' => (int) ($row->revenue ?? 0),
        ];
    }

    /**
     * Revenue from won leads since `$from`.
     */
    public static function revenueSince(CarbonInterface $from): float
    {
        return (float) self::between($from)['revenue'];
    }

    /**
     * A SQL CASE expression mapping `leads.package_slug` to the configured
     * package price, with its bindings.
     *
     * @return array{0: string, 1: list<int|string>}
     */
    private static function packagePriceCase(): array
    {
        $packages = ServicePackages::all();

        if ($packages === []) {
            return ['0', []];
        }

        $sql = 'CASE leads.package_slug';
        $bindings = [];

        foreach ($packages as $package) {
            $sql .= ' WHEN ? THEN ?';
            $bindings[] = $package->slug;
            $bindings[] = $package->priceEur;
        }

        return [$sql.' ELSE 0 END', $bindings];
    }

    /**
     * Prevent instantiation; this is a stateless query helper.
     */
    private function __construct() {}
}
