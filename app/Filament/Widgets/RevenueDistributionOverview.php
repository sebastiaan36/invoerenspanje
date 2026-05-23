<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Pages\StaffelBreakdown;
use App\Models\Lead;
use App\Services\Packages\ServicePackages;
use App\Services\Revenue\RevenueDistribution;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

final class RevenueDistributionOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $yearStart = Carbon::now()->startOfYear();

        $revenueThisYear = Lead::whereNotNull('package_slug')
            ->whereDate('created_at', '>=', $yearStart)
            ->get()
            ->sum(fn (Lead $lead) => ServicePackages::findBySlug($lead->package_slug)?->priceEur ?? 0);

        $distribution = new RevenueDistribution((float) $revenueThisYear);

        $year = Carbon::now()->year;

        return [
            Stat::make('Marketing budget', '€ '.number_format($distribution->marketing, 0, ',', '.'))
                ->description("10% van omzet {$year}")
                ->color('warning'),

            Stat::make('Sebastiaan', '€ '.number_format($distribution->sebastiaan, 0, ',', '.'))
                ->description("Staffel-aandeel {$year} — klik voor details")
                ->color('primary')
                ->url(StaffelBreakdown::getUrl()),

            Stat::make('Maikel', '€ '.number_format($distribution->maikel, 0, ',', '.'))
                ->description("Aandeel {$year}")
                ->color('success'),
        ];
    }
}
