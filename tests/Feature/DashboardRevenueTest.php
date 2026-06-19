<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\PackageStatsOverview;
use App\Models\Lead;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
