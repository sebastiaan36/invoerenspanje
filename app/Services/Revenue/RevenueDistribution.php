<?php

declare(strict_types=1);

namespace App\Services\Revenue;

/**
 * Berekent de omzetverdeling tussen marketing, Sebastiaan en Maikel.
 *
 * Staffel op bruto-omzet (reset-datum instelbaar via config):
 *   - 10% marketing (altijd)
 *   - Sebastiaan: 20% over €0–€10k | 15% over €10k–€30k | 10% boven €30k
 *   - Maikel: omzet − marketing − Sebastiaan
 */
final readonly class RevenueDistribution
{
    public float $marketing;

    public float $sebastiaan;

    public float $maikel;

    public function __construct(public float $revenue)
    {
        $this->marketing = round($revenue * 0.10, 2);
        $this->sebastiaan = round(self::calculateSebastiaanShare($revenue), 2);
        $this->maikel = round($revenue - $this->marketing - $this->sebastiaan, 2);
    }

    private static function calculateSebastiaanShare(float $revenue): float
    {
        $share = 0.0;

        // 20% over de eerste €10.000
        $share += min($revenue, 10_000) * 0.20;

        // 15% over €10.000 – €30.000
        $share += max(0, min($revenue, 30_000) - 10_000) * 0.15;

        // 10% over alles boven €30.000
        $share += max(0, $revenue - 30_000) * 0.10;

        return $share;
    }
}
