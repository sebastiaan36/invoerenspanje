<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Services\Revenue\WonLeadRevenue;
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

        $wonThisMonth = WonLeadRevenue::between($monthStart);
        $wonThisYear = WonLeadRevenue::between($yearStart);

        $totalLeadsThisMonth = Lead::where('created_at', '>=', $monthStart)->count();
        $totalLeadsThisYear = Lead::where('created_at', '>=', $yearStart)->count();

        $wonLeadsThisMonth = Lead::where('status', Lead::STATUS_WON)
            ->where('won_at', '>=', $monthStart)
            ->count();

        $wonLeadsThisYear = Lead::where('status', Lead::STATUS_WON)
            ->where('won_at', '>=', $yearStart)
            ->count();

        // Conversion is per cohort: of the leads that came in this period, how many are won by now.
        $wonCohortThisMonth = Lead::where('status', Lead::STATUS_WON)
            ->where('created_at', '>=', $monthStart)
            ->count();

        $wonCohortThisYear = Lead::where('status', Lead::STATUS_WON)
            ->where('created_at', '>=', $yearStart)
            ->count();

        $conversionThisMonth = $totalLeadsThisMonth > 0
            ? round($wonCohortThisMonth / $totalLeadsThisMonth * 100, 1)
            : 0.0;

        $conversionThisYear = $totalLeadsThisYear > 0
            ? round($wonCohortThisYear / $totalLeadsThisYear * 100, 1)
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
                ->description("{$wonCohortThisMonth} van {$totalLeadsThisMonth} leads")
                ->color('success'),

            Stat::make('Conversie dit jaar', number_format($conversionThisYear, 1, ',', '.').'%')
                ->description("{$wonCohortThisYear} van {$totalLeadsThisYear} leads")
                ->color('success'),

            Stat::make('Pakketten deze maand', $wonThisMonth['count'])
                ->description($now->translatedFormat('F Y'))
                ->color('primary'),

            Stat::make('Pakketten dit jaar', $wonThisYear['count'])
                ->description((string) $now->year)
                ->color('primary'),

            Stat::make('Omzet deze maand', '€ '.number_format($wonThisMonth['revenue'], 0, ',', '.'))
                ->description($now->translatedFormat('F Y'))
                ->color('success'),

            Stat::make('Omzet dit jaar', '€ '.number_format($wonThisYear['revenue'], 0, ',', '.'))
                ->description((string) $now->year)
                ->color('success'),
        ];
    }
}
