<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Spaanse import-kosten — IEDMT, marktwaarde-afschrijving en vaste kosten
|--------------------------------------------------------------------------
|
| ⚠ Onderstaande bedragen zijn PLACEHOLDER. De IEDMT-percentages onder
| 'autonomias.default' komen overeen met de algemene rijksregeling, maar
| autonome regio's mogen hier vanaf wijken (Canarias, La Rioja, e.a.).
| Vaste kosten en de marktwaarde-tabel moeten geverifieerd worden tegen
| recente Boletín Oficial del Estado / DGT-publicaties voor productie.
|
| Bron-referenties:
|   - Ley 38/1992 (Impuestos Especiales) — IEDMT-grondslag
|   - Orden Ministerial Hacienda — jaarlijkse marktwaarde-tabellen
|   - Real Decreto 750/2010 — IEDMT bij verandering van residentie
|   - https://sede.dgt.gob.es — matriculatie-tarieven
|
*/

return [

    /*
     * IEDMT (Impuesto Especial sobre Determinados Medios de Transporte)
     * — Spaanse registratiebelasting op basis van CO2 (g/km).
     *
     * Brackets zijn cumulatief: een waarde valt in de eerste schijf waarvoor
     * `co2_max_g_per_km` >= CO2 (of null voor open eindschijf).
     */
    'autonomias' => [

        'default' => [
            'label' => 'Algemene tarieven (meeste autonome regio\'s)',
            'iedmt_brackets' => [
                ['co2_max_g_per_km' => 120,  'rate_pct' => 0.0],
                ['co2_max_g_per_km' => 159,  'rate_pct' => 4.75],
                ['co2_max_g_per_km' => 199,  'rate_pct' => 9.75],
                ['co2_max_g_per_km' => null, 'rate_pct' => 14.75],
            ],
        ],

        // Costa del Sol valt in Andalucía, dat de landelijke tarieven volgt.
        // Dit is voor nu de enige geactiveerde regio in de UI.
        // TODO: bij uitbreiding naar Costa Brava (Cataluña) of Costa Blanca
        // (Valencia) extra entries toevoegen — daar geldt 16% in de hoogste schijf.
        'costa_del_sol' => [
            'label' => 'Costa del Sol (Andalucía) — landelijke tarieven',
            'iedmt_brackets' => [
                ['co2_max_g_per_km' => 120,  'rate_pct' => 0.0],
                ['co2_max_g_per_km' => 159,  'rate_pct' => 4.75],
                ['co2_max_g_per_km' => 199,  'rate_pct' => 9.75],
                ['co2_max_g_per_km' => null, 'rate_pct' => 14.75],
            ],
        ],
    ],

    /*
     * Vaste kosten bij import en matriculatie in Spanje.
     * Worden ongewijzigd opgeteld bij de IEDMT.
     *
     * ⚠ Bedragen zijn schattingen — pas aan voor je eigen partner-tarieven.
     */
    'fixed_costs' => [
        ['key' => 'homologatie',      'label' => 'Homologatie / ITV-conformiteit', 'amount_eur' => 250],
        ['key' => 'itv',              'label' => 'ITV-keuring (eerste matriculatie)', 'amount_eur' => 100],
        ['key' => 'matriculatie',     'label' => 'Matriculatie-tarief DGT',       'amount_eur' => 100],
        ['key' => 'kentekenplaten',   'label' => 'Spaanse kentekenplaten',        'amount_eur' => 30],
        ['key' => 'gestoria',         'label' => 'Gestoría (administratiekantoor)', 'amount_eur' => 180],
        ['key' => 'vertaling',        'label' => 'Beëdigde vertalingen documenten', 'amount_eur' => 90],
    ],

    /*
     * Forfaitaire marktwaarde-afschrijving op basis van leeftijd.
     * Toegepast op `catalogusprijs` uit de RDW om de Spaanse "valor de
     * mercado" te benaderen waarover IEDMT geheven wordt.
     *
     * In Spanje wordt jaarlijks een Orden Ministerial gepubliceerd met
     * exacte tabellen per merk/model. Deze vereenvoudiging is voldoende
     * voor een indicatie. Format: [years_min_inclusive, years_max_exclusive, depreciation_pct].
     */
    'market_value_depreciation_table' => [
        [0,  1,   0],
        [1,  2,   20],
        [2,  3,   30],
        [3,  4,   40],
        [4,  5,   50],
        [5,  6,   55],
        [6,  7,   60],
        [7,  8,   65],
        [8,  9,   70],
        [9,  10,  75],
        [10, 12,  80],
        [12, 15,  85],
        [15, 999, 90],
    ],

    /*
     * Vrijstellingen — zie Real Decreto 750/2010.
     */
    'exemptions' => [
        // Bij verhuizing van fiscale residentie naar Spanje is IEDMT-vrijstelling
        // mogelijk wanneer de auto >= 6 maanden in eigendom was vóór de verhuizing
        // en aan diverse voorwaarden voldaan wordt.
        'residency_change' => [
            'reason' => 'IEDMT-vrijstelling op grond van verandering van fiscale residentie naar Spanje (Real Decreto 750/2010). Vaste kosten blijven van toepassing.',
        ],
    ],

    /*
     * Notes die afhankelijk van het voertuig automatisch worden toegevoegd.
     */
    'notes' => [
        'commercial_vehicle' => 'Voor bedrijfsauto\'s gelden aparte regels bij de inschrijving in Spanje (mixto / comercial / turismo classificatie). Wij behandelen dit individueel in de offerte.',
    ],

];
