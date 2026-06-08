<?php

declare(strict_types=1);

return [
    'accent' => '#c0683f',
    'tiles' => 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
    'attribution' => '&copy; OpenStreetMap &copy; CARTO',

    // Beeldkader waarop de kaart inzoomt (zuidwest- en noordoosthoek).
    'bounds' => [[36.39, -5.28], [36.87, -3.84]],

    // Eén gesloten polygoon (sluit automatisch terug naar het eerste punt → geen overlap).
    'gebied' => [
        // westgrens: vanaf de kust bij Manilva omhoog langs de bergen
        [36.40, -5.27], [36.55, -5.05], [36.66, -4.92], [36.75, -4.86],
        // noordwest: boven Álora en Pizarra
        [36.86, -4.80], [36.87, -4.55],
        // noord: over de Montes de Málaga
        [36.86, -4.40], [36.85, -4.26],
        // naar de oostkust bij Nerja
        [36.78, -3.86], [36.74, -3.85],
        // terug langs de Costa del Sol richting het westen
        [36.72, -4.10], [36.71, -4.42], [36.59, -4.55], [36.54, -4.62],
        [36.51, -4.88], [36.46, -5.15],
    ],

    'plaatsen' => [
        ['naam' => 'Málaga', 'lat' => 36.7213, 'lng' => -4.4214],
        ['naam' => 'Alhaurín de la Torre', 'lat' => 36.6589, 'lng' => -4.5601],
        ['naam' => 'Alhaurín el Grande', 'lat' => 36.6427, 'lng' => -4.6856],
        ['naam' => 'Coín', 'lat' => 36.6592, 'lng' => -4.7561],
        ['naam' => 'Monda', 'lat' => 36.6314, 'lng' => -4.8244],
        ['naam' => 'Pizarra', 'lat' => 36.7660, 'lng' => -4.7000],
        ['naam' => 'Álora', 'lat' => 36.8222, 'lng' => -4.7028],
    ],
];
