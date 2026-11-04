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
     * Worden opgeteld bij de IEDMT.
     *
     * `amount_eur` = eindbedrag (geen btw, bijv. overheidsleges).
     * `amount_excl_vat_eur` + `vat_pct` = bedrag excl. btw; de klant betaalt
     * het bedrag inclusief btw (zie App\Services\SpainImport\SpainFixedCosts).
     *
     * ⚠ Bedragen zijn schattingen — pas aan voor je eigen partner-tarieven.
     */
    'fixed_costs' => [
        ['key' => 'tasas',                 'label' => 'Tasas DGT (registratieleges)',    'amount_eur' => 125],
        ['key' => 'ficha_tecnica',         'label' => 'Ficha Técnica (technisch rapport)', 'amount_excl_vat_eur' => 85, 'vat_pct' => 21],
        ['key' => 'honorarios',            'label' => 'Honorarios gestoría (Spaans administratiekantoor)', 'amount_excl_vat_eur' => 181.50, 'vat_pct' => 21],
        ['key' => 'matriculas_temporales', 'label' => 'Tijdelijke kentekenplaten (optioneel)', 'amount_excl_vat_eur' => 150, 'vat_pct' => 21],
        ['key' => 'itv_keuring',          'label' => 'ITV-keuring (ca. € 150–160)', 'amount_eur' => 155],
    ],

    /*
     * Notities die altijd worden meegestuurd met het resultaat (niet afhankelijk van voertuigtype).
     */
    'static_notes' => [
        'De Ficha Técnica vervalt indien u een COC-document aanlevert.',
        'Tijdelijke kentekenplaten (matrículas temporales) zijn niet verplicht, maar wel nodig om met een geëxporteerde auto naar de ITV te rijden en te blijven rijden.',
        'De ITV-keuring betaalt u zelf rechtstreeks aan de balie van het keuringsstation.',
        'De bedragen voor de Ficha Técnica, de gestoría en de tijdelijke kentekenplaten zijn inclusief 21% IVA (Spaanse btw).',
    ],

    /*
     * Waardebepaling volgens de Orden HAC/1501/2025 (BOE-A-2025-26357):
     *
     *  1. Nieuwwaarde = Hacienda "precio medio de venta" (Anexo I) voor het
     *     model; staat het model er niet in, dan de catalogusprijs nieuw (RDW).
     *  2. Marktwaarde = nieuwwaarde × percentage uit Anexo IV op basis van de
     *     volle jaren sinds de eerste toelating (in welk land dan ook).
     *  3. IEDMT = marktwaarde × IEDMT-tarief.
     *
     * De optionele correctie uit art. 5, regel 2 (marktwaarde ÷ (1 + btw-tarief +
     * IEDMT-tarief) voor auto's die eerder in het buitenland ingeschreven waren)
     * passen wij bewust NIET toe: we rekenen de IEDMT over de volle marktwaarde.
     */
    'hacienda' => [
        // Jaar van de prijstabel in database/data/hacienda/precios_medios_{jaar}.csv.
        'table_year' => 2026,

        // Art. 5, regel 2: "se podrá utilizar" — de correctie is toegestaan, niet verplicht.
        // Uit: de IEDMT wordt over de marktwaarde (na afschrijving) berekend.
        'apply_foreign_registration_correction' => false,
    ],

    /*
     * Anexo IV — percentage van de nieuwwaarde dat overblijft, per vol
     * gebruiksjaar sinds de eerste toelating. Index = aantal volle jaren;
     * vanaf 12 jaar blijft het 10%.
     */
    'hacienda_retention_pct_by_full_years' => [100, 84, 67, 56, 47, 39, 34, 28, 24, 19, 17, 13, 10],

    /*
     * Spaans btw-tarief (IVA) dat gold op de datum van eerste toelating.
     * Format: [vanaf_datum (inclusief), tarief_pct]; de laatste die van toepassing is wint.
     */
    'iva_rate_history' => [
        ['1900-01-01', 16],
        ['2010-07-01', 18],
        ['2012-09-01', 21],
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
        'unknown_co2' => 'De CO2-uitstoot van deze auto is niet bekend bij de RDW. Zonder aantoonbare CO2-waarde (CoC) rekent Hacienda het hoogste tarief van 14,75%.',
        'catalogue_price_fallback' => 'Dit model staat niet in de Hacienda-prijstabel. De registratiebelasting is geschat op basis van de Nederlandse catalogusprijs nieuw; wij controleren de exacte Hacienda-waarde in de offerte.',
    ],

];
