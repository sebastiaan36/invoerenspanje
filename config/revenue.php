<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Omzetverdeling (App\Services\Revenue\RevenueDistribution)
|--------------------------------------------------------------------------
|
| Percentages als fractie (0.10 = 10%) over de bruto-omzet.
|
|   - marketing: altijd dit deel van de omzet.
|   - sebastiaan_tiers: staffel; elke schijf geldt voor de omzet tot en met
|     `up_to_eur` (null = alles daarboven).
|   - Maikel krijgt de rest: omzet − marketing − Sebastiaan.
|
*/

return [

    'marketing_share' => 0.10,

    'sebastiaan_tiers' => [
        ['up_to_eur' => 10_000, 'share' => 0.20],
        ['up_to_eur' => 30_000, 'share' => 0.15],
        ['up_to_eur' => null, 'share' => 0.10],
    ],

];
