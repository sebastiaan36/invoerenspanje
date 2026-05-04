<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| BPM-tarieven en forfaitaire afschrijvingstabel
|--------------------------------------------------------------------------
|
| Implementatie volgens plan.md §236–496 (addendum Fase 2).
|
| ⚠ Alleen 2019 is hieronder volledig ingevuld als referentie-jaar
|   (komt overeen met het uitgewerkte voorbeeld in het plan).
|
| Voor productie:
|   - Vul jaar-tarieven aan voor 2007 t/m heden via de jaarlijkse
|     Belastingdienst-tabellen (Staatscourant).
|   - Vanaf 2025 geldt voor volledig elektrische voertuigen een vast
|     bedrag van € 600. Vul `ev_fixed` daarvoor in.
|   - Verifieer de forfaitaire afschrijvingstabel jaarlijks tegen de
|     Uitvoeringsregeling BPM 1992.
|
| Bron-referenties:
|   - https://www.belastingdienst.nl/.../bpm/bpm_berekenen_en_betalen
|   - https://wetten.overheid.nl/jci1.3:c:BWBR0006035 (Uitvoeringsregeling BPM 1992)
|
*/

return [

    'eligibility_cutoff_date' => '2006-10-16',

    /*
     * Forfaitaire afschrijvingstabel.
     * Per tier: gevonden wanneer maanden <= max_months.
     * Resultaat: base_percentage + (months - base_months) * per_month.
     */
    'depreciation_table' => [
        ['max_months' => 1,   'base_months' => 0,   'base_percentage' => 0,    'per_month' => 0],
        ['max_months' => 3,   'base_months' => 1,   'base_percentage' => 0,    'per_month' => 6],
        ['max_months' => 5,   'base_months' => 3,   'base_percentage' => 12,   'per_month' => 3],
        ['max_months' => 9,   'base_months' => 5,   'base_percentage' => 18,   'per_month' => 1.5],
        ['max_months' => 18,  'base_months' => 9,   'base_percentage' => 28,   'per_month' => 1.0],
        ['max_months' => 30,  'base_months' => 18,  'base_percentage' => 37,   'per_month' => 0.833],
        ['max_months' => 42,  'base_months' => 30,  'base_percentage' => 47,   'per_month' => 0.833],
        ['max_months' => 54,  'base_months' => 42,  'base_percentage' => 57,   'per_month' => 0.75],
        ['max_months' => 66,  'base_months' => 54,  'base_percentage' => 66,   'per_month' => 0.583],
        ['max_months' => 78,  'base_months' => 66,  'base_percentage' => 73,   'per_month' => 0.5],
        ['max_months' => 90,  'base_months' => 78,  'base_percentage' => 79,   'per_month' => 0.417],
        ['max_months' => 102, 'base_months' => 90,  'base_percentage' => 84,   'per_month' => 0.333],
        ['max_months' => 114, 'base_months' => 102, 'base_percentage' => 88,   'per_month' => 0.333],
        ['max_months' => 300, 'base_months' => 114, 'base_percentage' => 92,   'per_month' => 0.043],
    ],

    /*
     * Per-jaar tarieven. Sleutel = jaar van datum_eerste_toelating.
     *
     * Schijven definiëren cumulatieve CO2-grenzen. Laatste schijf gebruikt
     * `max => null` voor de open eindschijf.
     *
     * 'ev_fixed' is het BPM-bedrag voor volledig elektrische voertuigen
     * (Brandstof = 'Elektriciteit') in dat jaar. 0 = vrijstelling.
     */
    'years' => [

        2019 => [
            'fixed_base' => 366,
            'brackets' => [
                ['max' => 71,   'rate' => 0],
                ['max' => 95,   'rate' => 68],
                ['max' => 139,  'rate' => 152],
                ['max' => 161,  'rate' => 236],
                ['max' => 192,  'rate' => 414],
                ['max' => null, 'rate' => 557],
            ],
            'diesel' => [
                'threshold' => 70,
                'rate_per_gram' => 87.38,
            ],
            'ev_fixed' => 0,
        ],

        // TODO 2007–2018 + 2020–2026: vul actuele tarieven in uit Staatscourant.
        // Vanaf 2025 geldt voor EV een vast bedrag van € 600 (`ev_fixed' => 600).
    ],

];
