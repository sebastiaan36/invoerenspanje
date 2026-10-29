<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Voorbeeldreviews (alleen voor de ontwerp-preview)
    |--------------------------------------------------------------------------
    |
    | Echte reviews beheer je in de admin onder "Reviews". Zolang daar geen
    | gepubliceerde review staat, toont de homepage geen reviewsectie.
    |
    | 'examples' zijn verzonnen voorbeelden om het ontwerp te bekijken. Ze
    | verschijnen alleen met REVIEWS_SHOW_EXAMPLES=true, nooit in productie,
    | en altijd met het label "Voorbeeld".
    |
    */

    'show_examples' => (bool) env('REVIEWS_SHOW_EXAMPLES', false),

    'examples' => [
        [
            'name' => 'Jan en Marieke',
            'location' => 'Estepona',
            'rating' => 5,
            'date' => '2026-09',
            'text' => 'We zagen enorm op tegen het papierwerk. Maikel regelde de ITV en de DGT, wij hoefden alleen te tekenen. De BPM-teruggave kwam precies zoals berekend.',
            'source' => 'Google',
        ],
        [
            'name' => 'Peter',
            'location' => 'Fuengirola',
            'rating' => 5,
            'date' => '2026-08',
            'text' => 'Duidelijke vaste prijs vooraf en elke stap terug te zien in het portaal. Binnen zes weken reed ik op Spaanse platen.',
            'source' => 'Google',
        ],
        [
            'name' => 'Anouk',
            'location' => 'Nerja',
            'rating' => 4,
            'date' => '2026-07',
            'text' => 'Fijn om alles in het Nederlands te kunnen vragen. De ITV moest één keer opnieuw, maar dat werd netjes voor me geregeld.',
            'source' => 'Google',
        ],
    ],

];
