<?php

declare(strict_types=1);

return [
    // Terracotta uit DESIGN.md: hsl(17 55% 42%).
    'accent' => '#a65230',
    'tiles' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
    'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>-bijdragers',

    // Beeldkader waarop de kaart inzoomt (zuidwest- en noordoosthoek).
    'bounds' => [[36.39, -5.28], [36.87, -3.84]],

    // Eén gesloten polygoon, met de klok mee vanaf Manilva. Leaflet sluit hem
    // zelf; WerkgebiedKaart.vue rondt de hoeken af.
    'gebied' => [
        // westgrens: vanaf Manilva omhoog langs de bergen achter Estepona
        [36.37, -5.30], [36.47, -5.24], [36.56, -5.08], [36.66, -4.92],
        [36.75, -4.86],
        // noordwest: boven Álora en Pizarra
        [36.86, -4.80], [36.87, -4.55],
        // noord: over de Montes de Málaga
        [36.86, -4.40], [36.85, -4.26],
        // naar de oostkust bij Nerja
        [36.78, -3.86],
        // terug langs de Costa del Sol richting het westen: de kustlijn uit
        // OpenStreetMap, ca. 1,5 km de zee in geschoven en vereenvoudigd
        [36.7366, -3.8500], [36.7125, -3.9558], [36.7348, -4.0675], [36.7121, -4.1064],
        [36.7015, -4.1774], [36.6979, -4.3266], [36.7080, -4.3921], [36.6883, -4.4037],
        [36.6872, -4.4233], [36.5834, -4.4998], [36.5522, -4.5915], [36.4948, -4.6312],
        [36.4691, -4.7416], [36.4934, -4.8604], [36.4921, -4.9071], [36.4467, -5.0006],
        [36.4376, -5.0752], [36.3993, -5.1501], [36.4001, -5.1700], [36.3699, -5.2042],
        [36.3350, -5.2193],
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
