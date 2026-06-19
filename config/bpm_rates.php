<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| BPM-tarieven en forfaitaire afschrijvingstabel
|--------------------------------------------------------------------------
|
| Officiële tarieven personenauto's, overgenomen uit de Belastingdienst-
| brochure "Bpm-tarieven (vanaf 1993)":
|   https://download.belastingdienst.nl/belastingdienst/docs/bpm_tarieven_bpm0651z16fd.pdf
|
| Tarieven zijn gedefinieerd per PERIODE (datum eerste toelating), niet per
| kalenderjaar, omdat sommige jaren een tariefwijziging halverwege kennen
| (bijv. 1 juli 2020 i.v.m. de WLTP-overgang).
|
| Per periode geldt voor de personenauto de officiële schijventabel:
|   bpm = fixed_base + som over de schijven van (grammen in schijf × rate)
| Dit reproduceert exact de Belastingdienst-tabel (kolommen I/II/III/IV):
|   - fixed_base = het bedrag bij 0 g/km (kolom III van de eerste schijf)
|   - elke schijf: max = bovengrens (kolom II), rate = bedrag per gram (kolom IV)
|
| Diesel:
|   - 2015 en later: zelfde schijven als benzine + een dieseltoeslag.
|   - 2013/2014: een EIGEN dieselschijventabel (diesel_brackets / diesel_fixed_base)
|     + de dieseltoeslag.
|
| ev_fixed = BPM-bedrag voor volledig elektrische auto's (0 g/km). Tot 2025
| gold een vrijstelling (0); vanaf 2025 betaalt een EV het vaste basisbedrag.
|
| PHEV (plug-in hybride): had 2017–2024 een EIGEN, steiler tarief over de
| gewogen CO2-uitstoot (phev_fixed_base / phev_brackets). Daarbuiten (2013–2016
| en vanaf 2025) gold het gewone personenautotarief. Detectie en de keuze van
| de gewogen CO2 gebeurt in de RDW-laag (zie RdwService + BpmInput).
|
| Bestelauto (voertuigsoort 'Bedrijfsauto'): pas vanaf 2025 CO2-gebaseerd
| (bestelauto_brackets: vast bedrag per gram vanaf 0 g/km). Tot 2025 was het
| prijs-gebaseerd (37,7% netto-catalogusprijs) en niet uit de RDW-CO2 af te
| leiden; de calculator geeft dan een ruwe indicatie op het oudste
| bestelautotarief (2025) met een nadrukkelijke waarschuwing.
|
| LET OP — bekende beperking:
|   - Vóór 2013 was de BPM (personenauto) gebaseerd op de netto-catalogusprijs
|     (geen CO2). Die jaren staan niet in deze tabel; de calculator valt terug
|     op 2013 met een nadrukkelijke waarschuwing (ruwe indicatie).
*/

return [

    'eligibility_cutoff_date' => '2006-10-16',

    /*
     * Forfaitaire afschrijvingstabel (Uitvoeringsregeling BPM 1992).
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
     * Tarieven per periode (datum eerste toelating). Gesorteerd van oud naar
     * nieuw. De laatste schijf gebruikt `max => null` (open eindschijf).
     */
    'periods' => [

        [
            'from' => '2013-01-01', 'to' => '2013-12-31',
            'fixed_base' => 0,
            'brackets' => [
                ['max' => 95,   'rate' => 0],
                ['max' => 140,  'rate' => 125],
                ['max' => 208,  'rate' => 148],
                ['max' => 229,  'rate' => 276],
                ['max' => null, 'rate' => 551],
            ],
            'diesel_fixed_base' => 0,
            'diesel_brackets' => [
                ['max' => 88,   'rate' => 0],
                ['max' => 131,  'rate' => 125],
                ['max' => 192,  'rate' => 148],
                ['max' => 215,  'rate' => 276],
                ['max' => null, 'rate' => 551],
            ],
            'diesel' => ['threshold' => 70, 'rate_per_gram' => 56.13],
            'ev_fixed' => 0,
        ],

        [
            'from' => '2014-01-01', 'to' => '2014-12-31',
            'fixed_base' => 0,
            'brackets' => [
                ['max' => 88,   'rate' => 0],
                ['max' => 124,  'rate' => 105],
                ['max' => 182,  'rate' => 126],
                ['max' => 203,  'rate' => 237],
                ['max' => null, 'rate' => 474],
            ],
            'diesel_fixed_base' => 0,
            'diesel_brackets' => [
                ['max' => 85,   'rate' => 0],
                ['max' => 120,  'rate' => 105],
                ['max' => 175,  'rate' => 126],
                ['max' => 197,  'rate' => 237],
                ['max' => null, 'rate' => 474],
            ],
            'diesel' => ['threshold' => 70, 'rate_per_gram' => 72.93],
            'ev_fixed' => 0,
        ],

        [
            'from' => '2015-01-01', 'to' => '2015-12-31',
            'fixed_base' => 175,
            'brackets' => [
                ['max' => 82,   'rate' => 6],
                ['max' => 110,  'rate' => 69],
                ['max' => 160,  'rate' => 112],
                ['max' => 180,  'rate' => 217],
                ['max' => null, 'rate' => 434],
            ],
            'diesel' => ['threshold' => 70, 'rate_per_gram' => 86],
            'ev_fixed' => 0,
        ],

        [
            'from' => '2016-01-01', 'to' => '2016-12-31',
            'fixed_base' => 175,
            'brackets' => [
                ['max' => 79,   'rate' => 6],
                ['max' => 106,  'rate' => 69],
                ['max' => 155,  'rate' => 124],
                ['max' => 174,  'rate' => 239],
                ['max' => null, 'rate' => 478],
            ],
            'diesel' => ['threshold' => 67, 'rate_per_gram' => 86.43],
            'ev_fixed' => 0,
        ],

        [
            'from' => '2017-01-01', 'to' => '2017-12-31',
            'fixed_base' => 353,
            'brackets' => [
                ['max' => 76,   'rate' => 2],
                ['max' => 102,  'rate' => 66],
                ['max' => 150,  'rate' => 145],
                ['max' => 168,  'rate' => 238],
                ['max' => null, 'rate' => 475],
            ],
            'phev_fixed_base' => 0,
            'phev_brackets' => [
                ['max' => 30,   'rate' => 20],
                ['max' => 50,   'rate' => 90],
                ['max' => null, 'rate' => 300],
            ],
            'diesel' => ['threshold' => 65, 'rate_per_gram' => 86.69],
            'ev_fixed' => 0,
        ],

        [
            'from' => '2018-01-01', 'to' => '2018-12-31',
            'fixed_base' => 356,
            'brackets' => [
                ['max' => 73,   'rate' => 2],
                ['max' => 98,   'rate' => 63],
                ['max' => 144,  'rate' => 139],
                ['max' => 162,  'rate' => 229],
                ['max' => null, 'rate' => 458],
            ],
            'phev_fixed_base' => 0,
            'phev_brackets' => [
                ['max' => 30,   'rate' => 19],
                ['max' => 50,   'rate' => 87],
                ['max' => null, 'rate' => 289],
            ],
            'diesel' => ['threshold' => 63, 'rate_per_gram' => 87.38],
            'ev_fixed' => 0,
        ],

        [
            'from' => '2019-01-01', 'to' => '2019-12-31',
            'fixed_base' => 360,
            'brackets' => [
                ['max' => 71,   'rate' => 2],
                ['max' => 95,   'rate' => 60],
                ['max' => 139,  'rate' => 131],
                ['max' => 156,  'rate' => 215],
                ['max' => null, 'rate' => 429],
            ],
            'phev_fixed_base' => 0,
            'phev_brackets' => [
                ['max' => 30,   'rate' => 27],
                ['max' => 50,   'rate' => 113],
                ['max' => null, 'rate' => 271],
            ],
            'diesel' => ['threshold' => 61, 'rate_per_gram' => 88.43],
            'ev_fixed' => 0,
        ],

        // 1 jan 2020 t/m 30 juni 2020 (NEDC).
        [
            'from' => '2020-01-01', 'to' => '2020-06-30',
            'fixed_base' => 366,
            'brackets' => [
                ['max' => 68,   'rate' => 2],
                ['max' => 91,   'rate' => 59],
                ['max' => 133,  'rate' => 129],
                ['max' => 150,  'rate' => 212],
                ['max' => null, 'rate' => 424],
            ],
            'phev_fixed_base' => 0,
            'phev_brackets' => [
                ['max' => 30,   'rate' => 27],
                ['max' => 50,   'rate' => 111],
                ['max' => null, 'rate' => 267],
            ],
            'diesel' => ['threshold' => 59, 'rate_per_gram' => 89.85],
            'ev_fixed' => 0,
        ],

        // 1 juli 2020 t/m 31 dec 2020 (WLTP).
        [
            'from' => '2020-07-01', 'to' => '2020-12-31',
            'fixed_base' => 366,
            'brackets' => [
                ['max' => 90,   'rate' => 1],
                ['max' => 116,  'rate' => 57],
                ['max' => 162,  'rate' => 124],
                ['max' => 180,  'rate' => 204],
                ['max' => null, 'rate' => 408],
            ],
            'phev_fixed_base' => 0,
            'phev_brackets' => [
                ['max' => 34,   'rate' => 24],
                ['max' => 60,   'rate' => 83],
                ['max' => null, 'rate' => 199],
            ],
            'diesel' => ['threshold' => 80, 'rate_per_gram' => 78.82],
            'ev_fixed' => 0,
        ],

        [
            'from' => '2021-01-01', 'to' => '2021-12-31',
            'fixed_base' => 372,
            'brackets' => [
                ['max' => 86,   'rate' => 1],
                ['max' => 111,  'rate' => 60],
                ['max' => 155,  'rate' => 132],
                ['max' => 172,  'rate' => 216],
                ['max' => null, 'rate' => 432],
            ],
            'phev_fixed_base' => 0,
            'phev_brackets' => [
                ['max' => 34,   'rate' => 24],
                ['max' => 60,   'rate' => 84],
                ['max' => null, 'rate' => 202],
            ],
            'diesel' => ['threshold' => 77, 'rate_per_gram' => 83.59],
            'ev_fixed' => 0,
        ],

        [
            'from' => '2022-01-01', 'to' => '2022-12-31',
            'fixed_base' => 376,
            'brackets' => [
                ['max' => 84,   'rate' => 1],
                ['max' => 109,  'rate' => 62],
                ['max' => 152,  'rate' => 137],
                ['max' => 168,  'rate' => 224],
                ['max' => null, 'rate' => 448],
            ],
            'phev_fixed_base' => 0,
            'phev_brackets' => [
                ['max' => 34,   'rate' => 24],
                ['max' => 60,   'rate' => 85],
                ['max' => null, 'rate' => 204],
            ],
            'diesel' => ['threshold' => 75, 'rate_per_gram' => 86.67],
            'ev_fixed' => 0,
        ],

        [
            'from' => '2023-01-01', 'to' => '2023-12-31',
            'fixed_base' => 400,
            'brackets' => [
                ['max' => 82,   'rate' => 2],
                ['max' => 106,  'rate' => 68],
                ['max' => 148,  'rate' => 149],
                ['max' => 165,  'rate' => 244],
                ['max' => null, 'rate' => 488],
            ],
            'phev_fixed_base' => 0,
            'phev_brackets' => [
                ['max' => 34,   'rate' => 26],
                ['max' => 60,   'rate' => 91],
                ['max' => null, 'rate' => 217],
            ],
            'diesel' => ['threshold' => 73, 'rate_per_gram' => 94.30],
            'ev_fixed' => 0,
        ],

        [
            'from' => '2024-01-01', 'to' => '2024-12-31',
            'fixed_base' => 440,
            'brackets' => [
                ['max' => 80,   'rate' => 2],
                ['max' => 104,  'rate' => 76],
                ['max' => 145,  'rate' => 167],
                ['max' => 161,  'rate' => 274],
                ['max' => null, 'rate' => 549],
            ],
            'phev_fixed_base' => 0,
            'phev_brackets' => [
                ['max' => 34,   'rate' => 28],
                ['max' => 60,   'rate' => 100],
                ['max' => null, 'rate' => 239],
            ],
            'diesel' => ['threshold' => 71, 'rate_per_gram' => 106.07],
            'ev_fixed' => 0,
        ],

        // Vanaf 2025: volledig elektrische auto's betalen het vaste basisbedrag.
        [
            'from' => '2025-01-01', 'to' => '2025-12-31',
            'fixed_base' => 667,
            'brackets' => [
                ['max' => 79,   'rate' => 2],
                ['max' => 101,  'rate' => 79],
                ['max' => 141,  'rate' => 173],
                ['max' => 157,  'rate' => 284],
                ['max' => null, 'rate' => 568],
            ],
            'diesel' => ['threshold' => 70, 'rate_per_gram' => 109.87],
            'ev_fixed' => 667,
            // Bestelauto: CO2-gebaseerd vanaf 2025 — vast bedrag per gram vanaf 0 g/km.
            'bestelauto_fixed_base' => 0,
            'bestelauto_brackets' => [
                ['max' => null, 'rate' => 74.41],
            ],
        ],

        [
            'from' => '2026-01-01', 'to' => null,
            'fixed_base' => 687,
            'brackets' => [
                ['max' => 77,   'rate' => 2],
                ['max' => 100,  'rate' => 82],
                ['max' => 139,  'rate' => 181],
                ['max' => 155,  'rate' => 297],
                ['max' => null, 'rate' => 594],
            ],
            'diesel' => ['threshold' => 69, 'rate_per_gram' => 114.83],
            'ev_fixed' => 687,
            'bestelauto_fixed_base' => 0,
            'bestelauto_brackets' => [
                ['max' => null, 'rate' => 76.57],
            ],
        ],

    ],

];
