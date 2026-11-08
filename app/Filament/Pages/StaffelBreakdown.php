<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\Revenue\RevenueDistribution;
use App\Services\Revenue\WonLeadRevenue;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class StaffelBreakdown extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.staffel-breakdown';

    protected static ?string $title = 'Staffel — Sebastiaan';

    public function getViewData(): array
    {
        $yearStart = Carbon::now()->startOfYear();
        $year = Carbon::now()->year;

        $revenue = WonLeadRevenue::revenueSince($yearStart);

        $distribution = new RevenueDistribution($revenue);

        $tiers = self::tierRows($revenue);

        return [
            'year' => $year,
            'revenue' => $revenue,
            'marketing' => $distribution->marketing,
            'sebastiaan' => $distribution->sebastiaan,
            'maikel' => $distribution->maikel,
            'tiers' => $tiers,
            'marketingSharePct' => (float) config('revenue.marketing_share') * 100,
        ];
    }

    /**
     * Split the revenue over the staffel tiers from config/revenue.php.
     *
     * @return list<array{label: string, rate_pct: float, omzet: float, deel: float}>
     */
    public static function tierRows(float $revenue): array
    {
        /** @var list<array{up_to_eur: int|null, share: float}> $tiers */
        $tiers = config('revenue.sebastiaan_tiers');

        $rows = [];
        $lowerBound = 0;

        foreach ($tiers as $tier) {
            $upperBound = $tier['up_to_eur'];
            $tierRevenue = $upperBound === null
                ? max(0.0, $revenue - $lowerBound)
                : max(0.0, min($revenue, $upperBound) - $lowerBound);

            $rows[] = [
                'label' => $upperBound === null
                    ? 'Boven € '.number_format($lowerBound, 0, ',', '.')
                    : '€ '.number_format($lowerBound, 0, ',', '.').' – € '.number_format($upperBound, 0, ',', '.'),
                'rate_pct' => $tier['share'] * 100,
                'omzet' => (float) $tierRevenue,
                'deel' => round($tierRevenue * $tier['share'], 2),
            ];

            if ($upperBound !== null) {
                $lowerBound = $upperBound;
            }
        }

        return $rows;
    }
}
