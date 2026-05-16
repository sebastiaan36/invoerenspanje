<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Service-pakketten
|--------------------------------------------------------------------------
|
| Drie vaste pakketten die op de homepage en op /tarieven worden getoond.
| De `slug` waarden komen overeen met de toekomstige dossiers.pakket kolom
| zoals beschreven in plan.md §157.
|
| Wijzig prijzen of features hier — UI en lead-opslag pakken het op via
| ServicePackages helper.
|
*/

return [

    'list' => [

        [
            'slug' => 'basis',
            'name' => 'Basis',
            'price_eur' => 395,
            'tagline' => 'Voor wie zelf de regie wil houden',
            'recommended' => false,
            'features' => [
                'Aanvraag matriculación bij DGT',
                'Begeleiding bij het indienen van documenten',
                'U regelt zelf NIE, ITV, transport en uitvoer',
            ],
        ],

        [
            'slug' => 'compleet',
            'name' => 'Compleet',
            'price_eur' => 895,
            'tagline' => 'Wij regelen alles, u levert alleen documenten aan',
            'recommended' => true,
            'features' => [
                'Volledige matriculación bij DGT',
                'NIE-begeleiding indien nodig',
                'ITV-afspraak en -begeleiding',
                'Kentekenplaten en gemeentebelasting',
                'BPM-teruggave Nederland inbegrepen',
                'Communicatie met DGT namens u',
            ],
        ],

        [
            'slug' => 'compleet_plus',
            'name' => 'Compleet Plus',
            'price_eur' => 1495,
            'tagline' => 'Volledig zorgenpakket inclusief transport',
            'recommended' => false,
            'features' => [
                'Alles uit Compleet',
                'Auto-export bij RDW Nederland',
                'Transport NL → Spanje (via partner)',
                'Spaanse autoverzekering afsluiten',
                'Spaanse rijbewijsomwisseling indien gewenst',
            ],
        ],

    ],

];
