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
            ->where('status', 'gewonnen')
            ->whereDate('created_at', '>=', $monthStart)
            ->get();

        $leadsThisYear = Lead::whereNotNull('package_slug')
            ->where('status', 'gewonnen')
            ->whereDate('created_at', '>=', $yearStart)
            ->get();

        $revenueThisMonth = $leadsThisMonth->sum(
            fn (Lead $lead) => ServicePackages::findBySlug($lead->package_slug)?->priceEur ?? 0,
        );

        $revenueThisYear = $leadsThisYear->sum(
            fn (Lead $lead) => ServicePackages::findBySlug($lead->package_slug)?->priceEur ?? 0,
        );

        $totalLeadsThisMonth = Lead::whereDate('created_at', '>=', $monthStart)->count();
        $totalLeadsThisYear = Lead::whereDate('created_at', '>=', $yearStart)->count();

        $wonLeadsThisMonth = Lead::where('status', 'gewonnen')
            ->whereDate('created_at', '>=', $monthStart)
            ->count();

        $wonLeadsThisYear = Lead::where('status', 'gewonnen')
            ->whereDate('created_at', '>=', $yearStart)
            ->count();

        $conversionThisMonth = $totalLeadsThisMonth > 0
            ? round($wonLeadsThisMonth / $totalLeadsThisMonth * 100, 1)
            : 0.0;

        $conversionThisYear = $totalLeadsThisYear > 0
            ? round($wonLeadsThisYear / $totalLeadsThisYear * 100, 1)
            : 0.0;

        return [
            Stat::make('Leads deze maand', (string) $totalLeadsThisMonth)
                ->description($now->translatedFormat('F Y'))
                ->color('primary'),

            Stat::make('Leads dit jaar', (string) $totalLeadsThisYear)
                ->description((string) $now->year)
                ->color('primary'),

            Stat::make('Gewonnen deze maand', (string) $wonLeadsThisMonth)
                ->description($now->translatedFormat('F Y'))
                ->color('success'),

            Stat::make('Gewonnen dit jaar', (string) $wonLeadsThisYear)
                ->description((string) $now->year)
                ->color('success'),

            Stat::make('Conversie deze maand', number_format($conversionThisMonth, 1, ',', '.').'%')
                ->description("{$wonLeadsThisMonth} van {$totalLeadsThisMonth} leads")
                ->color('success'),

            Stat::make('Conversie dit jaar', number_format($conversionThisYear, 1, ',', '.').'%')
                ->description("{$wonLeadsThisYear} van {$totalLeadsThisYear} leads")
                ->color('success'),

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
