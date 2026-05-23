<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Lead;
use App\Services\Packages\ServicePackages;
use App\Services\Revenue\RevenueDistribution;
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

        $revenue = (float) Lead::whereNotNull('package_slug')
            ->whereDate('created_at', '>=', $yearStart)
            ->get()
            ->sum(fn (Lead $lead) => ServicePackages::findBySlug($lead->package_slug)?->priceEur ?? 0);

        $distribution = new RevenueDistribution($revenue);

        $tier1Revenue = min($revenue, 10_000);
        $tier2Revenue = max(0.0, min($revenue, 30_000) - 10_000);
        $tier3Revenue = max(0.0, $revenue - 30_000);

        $tier1Share = round($tier1Revenue * 0.20, 2);
        $tier2Share = round($tier2Revenue * 0.15, 2);
        $tier3Share = round($tier3Revenue * 0.10, 2);

        return [
            'year' => $year,
            'revenue' => $revenue,
            'marketing' => $distribution->marketing,
            'sebastiaan' => $distribution->sebastiaan,
            'maikel' => $distribution->maikel,
            'tier1Revenue' => $tier1Revenue,
            'tier2Revenue' => $tier2Revenue,
            'tier3Revenue' => $tier3Revenue,
            'tier1Share' => $tier1Share,
            'tier2Share' => $tier2Share,
            'tier3Share' => $tier3Share,
        ];
    }
}
