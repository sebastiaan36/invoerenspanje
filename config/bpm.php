<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| BPM (Belasting van Personenauto's en Motorrijwielen) — INDICATIVE
|--------------------------------------------------------------------------
|
| ⚠  ALLE BEDRAGEN IN DIT BESTAND ZIJN PLACEHOLDER-WAARDEN.
|
| Voor productie MOETEN deze tarieven gevalideerd worden tegen de officiële
| publicaties van de Belastingdienst en/of een fiscalist. Tarieven en de
| forfaitaire afschrijvingstabel veranderen jaarlijks.
|
| Bron-referenties (verifieer hier voor actuele cijfers):
|   - https://www.belastingdienst.nl/wps/wcm/connect/bldcontentnl/belastingdienst/zakelijk/auto_en_vervoer/belastingen_op_auto_en_motor/bpm/
|   - https://www.belastingdienst.nl/wps/wcm/connect/bldcontentnl/belastingdienst/zakelijk/auto_en_vervoer/belastingen_op_auto_en_motor/bpm/bpm_berekenen_en_betalen/forfaitaire_tabel
|
| De berekening is opzettelijk vereenvoudigd: één lineaire CO2-staffel met
| dieseltoeslag, plus de forfaitaire afschrijvingstabel. Voor de echte
| productie-implementatie moet je per jaar de werkelijke gestaffelde
| brackets implementeren.
|
*/

return [

    'disclaimer' => 'De BPM-indicatie is gebaseerd op vereenvoudigde tarieven en heeft uitsluitend een informatief karakter. Aan deze berekening kunnen geen rechten worden ontleend. Het uiteindelijke bedrag wordt door de Belastingdienst vastgesteld.',

    /*
     * Single linear bracket per registratiejaar — PLACEHOLDER.
     * TODO: vervang door werkelijke gestaffelde tarieven uit de jaarlijkse
     * BPM-tabel van de Belastingdienst.
     */
    'rates' => [
        // Default rate used when the registratiejaar valt buiten de gedefinieerde jaren
        'default' => [
            'co2_threshold_g_per_km' => 79,
            'fixed_voet_eur' => 440,
            'per_gram_above_threshold_eur' => 162,
            'diesel_surcharge_per_gram_eur' => 105.0,
            'diesel_surcharge_threshold_g_per_km' => 70,
        ],

        // Year-specific overrides. Populate per registratiejaar voor productie.
        // 2024 => [...],
        // 2023 => [...],
    ],

    /*
     * Forfaitaire afschrijvingstabel (Belastingdienst) — leeftijd → afschrijvings-percentage.
     * Format: [months_min_inclusive, months_max_exclusive, depreciation_pct].
     *
     * ⚠ Waarden hieronder zijn een redelijke benadering van de gepubliceerde tabel
     * voor 2024, maar moeten geverifieerd worden voor productie.
     */
    'depreciation_table' => [
        [0,   1,   0.0],
        [1,   3,   8.0],
        [3,   5,   12.0],
        [5,   9,   15.0],
        [9,   12,  19.0],
        [12,  18,  24.0],
        [18,  30,  37.0],
        [30,  42,  47.0],
        [42,  54,  55.0],
        [54,  66,  61.0],
        [66,  78,  66.0],
        [78,  90,  71.0],
        [90,  102, 75.0],
        [102, 114, 79.0],
        [114, 126, 82.0],
        [126, 162, 84.5],
        [162, 198, 86.5],
        [198, 234, 88.0],
        [234, 270, 89.5],
        [270, 306, 90.5],
        [306, 9999, 92.0],
    ],

    /*
     * Wettelijke minimum-restwaarde van BPM voor dieselvoertuigen — PLACEHOLDER.
     * In de praktijk hanteert de Belastingdienst een minimum restwaarde voor diesel
     * ongeacht leeftijd. Vul hier het actuele percentage in (bv. 12.0 = 12%).
     */
    'diesel_minimum_residual_pct' => 12.0,

];
