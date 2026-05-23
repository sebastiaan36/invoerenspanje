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
                'U regelt zelf de export in Nederland',
                'U levert de gevraagde documenten aan',
                'U levert COC (Ficha Técnica kost € 85 extra zonder COC)',
                'Wij verwerken de aanvraag richting DGT',
                'Groene platen leveren — u haalt ze op',
                'U regelt zelf de ITV-afspraak en gaat zelf',
                'Officieel kenteken en platen leveren — u haalt ze op',
            ],
        ],

        [
            'slug' => 'compleet',
            'name' => 'Compleet',
            'price_eur' => 895,
            'tagline' => 'Wij regelen alles, u levert alleen documenten aan',
            'recommended' => true,
            'features' => [
                'Alles uit Basis',
                'Export via 1 van onze partners in Nederland',
                'Wij regelen de Ficha Técnica indien COC ontbreekt (foto\'s op locatie)',
                'Doorverwijzing naar aangesloten verzekeraar',
                'Wij regelen de ITV-afspraak voor u',
            ],
        ],

        [
            'slug' => 'compleet_plus',
            'name' => 'Compleet Plus',
            'price_eur' => 1495,
            'tagline' => 'Volledig zorgenpakket — wij regelen alles van A tot Z',
            'recommended' => false,
            'features' => [
                'Alles uit Compleet',
                'Aankoopadvies auto in Nederland (30 min. call)',
                'Wij komen thuis voor foto\'s en documenten',
                'Wij exporteren de auto via 1 van onze partners',
                'Wij regelen het transport van de auto (ex. transportkosten)',
                'Doorverwijzing naar verzekeraar + meekijken en tips',
                'Wij gaan namens u naar de ITV-afspraak',
            ],
        ],

    ],

];
