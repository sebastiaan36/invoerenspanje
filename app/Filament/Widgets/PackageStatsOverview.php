<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Services\Packages\ServicePackages;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

final class PackageStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();

        $monthStart = $now->copy()->startOfMonth();
        $yearStart = $now->copy()->startOfYear();

        $leadsThisMonth = Lead::whereNotNull('package_slug')
            ->whereDate('created_at', '>=', $monthStart)
            ->get();

        $leadsThisYear = Lead::whereNotNull('package_slug')
            ->whereDate('created_at', '>=', $yearStart)
            ->get();

        $revenueThisMonth = $leadsThisMonth->sum(
            fn (Lead $lead) => ServicePackages::findBySlug($lead->package_slug)?->priceEur ?? 0,
        );

        $revenueThisYear = $leadsThisYear->sum(
            fn (Lead $lead) => ServicePackages::findBySlug($lead->package_slug)?->priceEur ?? 0,
        );

        return [
            Stat::make('Pakketten deze maand', $leadsThisMonth->count())
                ->description($now->translatedFormat('F Y'))
                ->color('primary'),

            Stat::make('Pakketten dit jaar', $leadsThisYear->count())
                ->description((string) $now->year)
                ->color('primary'),

            Stat::make('Omzet deze maand', '€ '.number_format($revenueThisMonth, 0, ',', '.'))
                ->description($now->translatedFormat('F Y'))
                ->color('success'),

            Stat::make('Omzet dit jaar', '€ '.number_format($revenueThisYear, 0, ',', '.'))
                ->description((string) $now->year)
                ->color('success'),
        ];
    }
}
