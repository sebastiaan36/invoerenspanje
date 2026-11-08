<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\StaffelBreakdown;
use App\Filament\Widgets\PackageStatsOverview;
use App\Filament\Widgets\RevenueDistributionOverview;
use App\Models\Dossier;
use App\Models\Lead;
use App\Services\Revenue\RevenueDistribution;
use App\Services\Revenue\WonLeadRevenue;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class DashboardRevenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_revenue_only_counts_won_leads(): void
    {
        // compleet = € 895
        Lead::factory()->gewonnen()->create(['package_slug' => 'compleet']);
        Lead::factory()->gewonnen()->create(['package_slug' => 'compleet']);

        // Non-won leads must be excluded from revenue.
        Lead::factory()->create(['status' => 'nieuw', 'package_slug' => 'compleet']);
        Lead::factory()->create(['status' => 'offerte', 'package_slug' => 'compleet']);
        Lead::factory()->create(['status' => 'verloren', 'package_slug' => 'compleet']);

        $stats = $this->invokeStats();

        $this->assertSame('€ 1.790', $stats['Omzet dit jaar']);
        $this->assertSame('€ 1.790', $stats['Omzet deze maand']);
        $this->assertSame(2, $stats['Pakketten dit jaar']);

        // 2 won out of 5 total leads = 40% conversion (all created this month).
        $this->assertSame('5', $stats['Leads dit jaar']);
        $this->assertSame('5', $stats['Leads deze maand']);
        $this->assertSame('2', $stats['Gewonnen dit jaar']);
        $this->assertSame('2', $stats['Gewonnen deze maand']);
        $this->assertSame('40,0%', $stats['Conversie dit jaar']);
        $this->assertSame('40,0%', $stats['Conversie deze maand']);
    }

    public function test_revenue_is_zero_without_won_leads(): void
    {
        Lead::factory()->create(['status' => 'nieuw', 'package_slug' => 'compleet']);

        $stats = $this->invokeStats();

        $this->assertSame('€ 0', $stats['Omzet dit jaar']);
        $this->assertSame(0, $stats['Pakketten dit jaar']);
    }

    public function test_won_at_is_set_when_a_lead_is_won_and_cleared_when_it_is_reopened(): void
    {
        $lead = Lead::factory()->create(['status' => 'offerte']);
        $this->assertNull($lead->won_at);

        $lead->update(['status' => Lead::STATUS_WON]);
        $this->assertNotNull($lead->fresh()->won_at);

        $lead->update(['status' => 'offerte']);
        $this->assertNull($lead->fresh()->won_at);
    }

    public function test_revenue_uses_the_dossier_fee_and_falls_back_to_the_package_price(): void
    {
        $withFee = Lead::factory()->gewonnen()->create(['package_slug' => 'compleet']);
        Dossier::factory()->create(['lead_id' => $withFee->id, 'service_fee_eur' => 650]);

        $withoutFee = Lead::factory()->gewonnen()->create(['package_slug' => 'basis']);
        Dossier::factory()->create(['lead_id' => $withoutFee->id, 'service_fee_eur' => null]);

        // No dossier at all: package price (compleet = € 895).
        Lead::factory()->gewonnen()->create(['package_slug' => 'compleet']);

        $this->assertSame(['count' => 3, 'revenue' => 650 + 395 + 895], WonLeadRevenue::between(now()->startOfYear()));

        $stats = $this->invokeStats();
        $this->assertSame('€ 1.940', $stats['Omzet dit jaar']);
    }

    public function test_revenue_is_booked_in_the_period_the_lead_was_won(): void
    {
        $this->travelTo(Carbon::parse('2026-11-15 12:00'));

        // Came in last year, won this month: counts this month.
        $lateWin = Lead::factory()->gewonnen()->create(['package_slug' => 'compleet']);
        $lateWin->forceFill(['created_at' => Carbon::parse('2025-12-20')])->saveQuietly();

        // Won last month: counts this year only.
        $lastMonth = Lead::factory()->gewonnen()->create(['package_slug' => 'basis']);
        $lastMonth->forceFill(['won_at' => Carbon::parse('2026-10-10')])->saveQuietly();

        // Won last year: not counted.
        $old = Lead::factory()->gewonnen()->create(['package_slug' => 'compleet_plus']);
        $old->forceFill(['won_at' => Carbon::parse('2025-11-30'), 'created_at' => Carbon::parse('2025-11-01')])->saveQuietly();

        $stats = $this->invokeStats();

        $this->assertSame('€ 895', $stats['Omzet deze maand']);
        $this->assertSame('€ 1.290', $stats['Omzet dit jaar']);
        $this->assertSame(1, $stats['Pakketten deze maand']);
        $this->assertSame(2, $stats['Pakketten dit jaar']);
    }

    public function test_distribution_widget_and_staffel_page_use_the_same_revenue(): void
    {
        $lead = Lead::factory()->gewonnen()->create(['package_slug' => 'compleet']);
        Dossier::factory()->create(['lead_id' => $lead->id, 'service_fee_eur' => 1000]);
        Lead::factory()->gewonnen()->create(['package_slug' => 'basis']);

        $expected = new RevenueDistribution(1395.0);

        $staffel = (new StaffelBreakdown)->getViewData();
        $this->assertSame(1395.0, $staffel['revenue']);
        $this->assertSame($expected->sebastiaan, $staffel['sebastiaan']);
        $this->assertEqualsWithDelta(
            $staffel['sebastiaan'],
            array_sum(array_column($staffel['tiers'], 'deel')),
            0.01,
        );

        $widget = new RevenueDistributionOverview;
        $method = new \ReflectionMethod($widget, 'getStats');

        /** @var list<Stat> $stats */
        $stats = $method->invoke($widget);
        $values = collect($stats)->mapWithKeys(fn (Stat $stat): array => [$stat->getLabel() => $stat->getValue()]);

        $this->assertSame('€ '.number_format($expected->marketing, 0, ',', '.'), $values['Marketing budget']);
        $this->assertSame('€ '.number_format($expected->maikel, 0, ',', '.'), $values['Maikel']);
    }

    /**
     * @return array<string, mixed>
     */
    private function invokeStats(): array
    {
        $widget = new PackageStatsOverview;

        $method = new \ReflectionMethod($widget, 'getStats');

        /** @var list<Stat> $stats */
        $stats = $method->invoke($widget);

        $result = [];

        foreach ($stats as $stat) {
            $result[$stat->getLabel()] = $stat->getValue();
        }

        return $result;
    }

    public function test_staffel_tiers_follow_the_revenue_config(): void
    {
        config(['revenue.sebastiaan_tiers' => [
            ['up_to_eur' => 5_000, 'share' => 0.30],
            ['up_to_eur' => null, 'share' => 0.05],
        ]]);

        $rows = StaffelBreakdown::tierRows(8_000.0);

        $this->assertCount(2, $rows);
        $this->assertSame('€ 0 – € 5.000', $rows[0]['label']);
        $this->assertSame(5_000.0, $rows[0]['omzet']);
        $this->assertSame(1_500.0, $rows[0]['deel']);
        $this->assertSame('Boven € 5.000', $rows[1]['label']);
        $this->assertSame(3_000.0, $rows[1]['omzet']);
        $this->assertSame(150.0, $rows[1]['deel']);
    }
}
