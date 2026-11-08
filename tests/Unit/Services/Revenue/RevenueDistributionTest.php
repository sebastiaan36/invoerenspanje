<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Revenue;

use App\Services\Revenue\RevenueDistribution;
use Tests\TestCase;

final class RevenueDistributionTest extends TestCase
{
    public function test_the_first_tier_applies_up_to_ten_thousand(): void
    {
        $distribution = new RevenueDistribution(8_000);

        $this->assertSame(800.0, $distribution->marketing);
        $this->assertSame(1_600.0, $distribution->sebastiaan);
        $this->assertSame(5_600.0, $distribution->maikel);
    }

    public function test_all_tiers_add_up_above_thirty_thousand(): void
    {
        // 20% × 10k + 15% × 20k + 10% × 10k = 2.000 + 3.000 + 1.000.
        $distribution = new RevenueDistribution(40_000);

        $this->assertSame(4_000.0, $distribution->marketing);
        $this->assertSame(6_000.0, $distribution->sebastiaan);
        $this->assertSame(30_000.0, $distribution->maikel);
    }

    public function test_the_tier_boundaries_are_inclusive(): void
    {
        $this->assertSame(2_000.0, (new RevenueDistribution(10_000))->sebastiaan);
        $this->assertSame(5_000.0, (new RevenueDistribution(30_000))->sebastiaan);
        $this->assertSame(0.0, (new RevenueDistribution(0))->sebastiaan);
    }

    public function test_the_percentages_come_from_config(): void
    {
        config([
            'revenue.marketing_share' => 0.05,
            'revenue.sebastiaan_tiers' => [['up_to_eur' => null, 'share' => 0.5]],
        ]);

        $distribution = new RevenueDistribution(1_000);

        $this->assertSame(50.0, $distribution->marketing);
        $this->assertSame(500.0, $distribution->sebastiaan);
        $this->assertSame(450.0, $distribution->maikel);
    }
}
