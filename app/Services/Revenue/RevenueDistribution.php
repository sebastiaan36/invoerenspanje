<?php

declare(strict_types=1);

namespace App\Services\Revenue;

/**
 * Berekent de omzetverdeling tussen marketing, Sebastiaan en Maikel.
 *
 * Staffel op bruto-omzet, ingesteld in config/revenue.php:
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
        $this->marketing = round($revenue * (float) config('revenue.marketing_share'), 2);
        $this->sebastiaan = round(self::calculateSebastiaanShare($revenue), 2);
        $this->maikel = round($revenue - $this->marketing - $this->sebastiaan, 2);
    }

    /**
     * Each tier applies its share to the revenue between the previous tier's
     * upper bound and its own (`up_to_eur` null = no upper bound).
     */
    private static function calculateSebastiaanShare(float $revenue): float
    {
        /** @var list<array{up_to_eur: int|float|null, share: float}> $tiers */
        $tiers = config('revenue.sebastiaan_tiers');

        $share = 0.0;
        $lowerBound = 0.0;

        foreach ($tiers as $tier) {
            $upperBound = $tier['up_to_eur'] === null ? $revenue : min($revenue, (float) $tier['up_to_eur']);
            $share += max(0.0, $upperBound - $lowerBound) * (float) $tier['share'];

            if ($tier['up_to_eur'] === null || $revenue <= $tier['up_to_eur']) {
                break;
            }

            $lowerBound = (float) $tier['up_to_eur'];
        }

        return $share;
    }
}
