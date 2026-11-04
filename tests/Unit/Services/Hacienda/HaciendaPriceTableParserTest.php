<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Hacienda;

use App\Services\Hacienda\HaciendaPriceTableParser;
use PHPUnit\Framework\TestCase;

final class HaciendaPriceTableParserTest extends TestCase
{
    private const string BOE_EXCERPT = <<<'TXT'
               Artículo 3. Determinación del valor de los vehículos automóviles
                         BOLETÍN OFICIAL DEL ESTADO
Núm. 308                                Martes 23 de diciembre de 2025                          Sec. I. Pág. 171583

                                                              ANEXO I

                                                             Marca: AUDI

                                                                   Periodo
                                                                                          Datos ficha técnica
                                                                  comercial                                                     2026
                               Modelo-Tipo                                                                                cv    Valor
                                                                                       N.º de    Tipo de                        euros
                                                                Inicio   Fin    C.C.                       P kW    cvf
   A3 1.8 T Ambition Plus / Quattro / Tiptronic 5p                   2000     2003      1781     4         G       132    12,43   180   22.800
   A3 1.8 T mbit Aut Plus / Tiptr. Ambit Plus / Quattro Ambition-
                                                                     2000     2003      1781     4         G       110    12,43   150   21.600
   Ambiente 5p
   A110 2P Coupe (2022-)                                         2022            1798     4         G       185            252   50.000
                                                                                                                                         cve: BOE-A-2025-26357
   Q4 e-tron 40                                                  2021                              Elc      150    29,11   204   46.300
   A4 Avant 40 TFSI Advanced S tronic 7 vel.                     2019     2022   1984     4         GyE     140    13,62   190   37.700
                                                             Marca: KGM
   Torres G15T Life GLP                                           2024            1497               S       120    10,41   163   27.800
                                                             Marca: PEUGEOT
   2008 1.2 PureTech 130 Allure                                   2020            1199     3         G       96     8,23    131   21.400

                     Precios medios de autocaravanas usadas durante el primer año posterior
                                                             Marca: ADRIA
   Coral 670 SL                                                   2020            2287     4         D       103    15,10   140   62.000
TXT;

    public function test_it_parses_the_columns_of_a_regular_row(): void
    {
        $rows = (new HaciendaPriceTableParser)->parse(self::BOE_EXCERPT);

        $this->assertSame([
            'brand' => 'AUDI',
            'model' => 'A3 1.8 T Ambition Plus / Quattro / Tiptronic 5p',
            'start_year' => 2000,
            'end_year' => 2003,
            'cc' => 1781,
            'fuel' => 'G',
            'kw' => 132,
            'cv' => 180,
            'price_eur' => 22800,
        ], $rows[0]);
    }

    public function test_it_joins_a_model_name_that_wraps_around_its_data_row(): void
    {
        $rows = (new HaciendaPriceTableParser)->parse(self::BOE_EXCERPT);

        $this->assertSame('A3 1.8 T mbit Aut Plus / Tiptr. Ambit Plus / Quattro Ambition- Ambiente 5p', $rows[1]['model']);
        $this->assertSame(21600, $rows[1]['price_eur']);
    }

    public function test_it_handles_missing_end_year_cc_and_cvf_columns(): void
    {
        $rows = (new HaciendaPriceTableParser)->parse(self::BOE_EXCERPT);

        $this->assertSame([2022, null, 1798, 185, 50000], [$rows[2]['start_year'], $rows[2]['end_year'], $rows[2]['cc'], $rows[2]['kw'], $rows[2]['price_eur']]);
        $this->assertSame(['Elc', null, null, 150], [$rows[3]['fuel'], $rows[3]['end_year'], $rows[3]['cc'], $rows[3]['kw']]);
        $this->assertSame('GyE', $rows[4]['fuel']);
    }

    public function test_a_cc_without_cylinder_count_is_not_read_as_end_year(): void
    {
        $rows = (new HaciendaPriceTableParser)->parse(self::BOE_EXCERPT);

        $this->assertSame(['KGM', 2024, null, 1497, 'S'], [$rows[5]['brand'], $rows[5]['start_year'], $rows[5]['end_year'], $rows[5]['cc'], $rows[5]['fuel']]);
    }

    public function test_a_model_name_that_starts_with_a_year_like_number_is_kept(): void
    {
        $rows = (new HaciendaPriceTableParser)->parse(self::BOE_EXCERPT);

        $this->assertSame(['PEUGEOT', '2008 1.2 PureTech 130 Allure'], [$rows[6]['brand'], $rows[6]['model']]);
    }

    public function test_motorhomes_are_not_included(): void
    {
        $rows = (new HaciendaPriceTableParser)->parse(self::BOE_EXCERPT);

        $this->assertCount(7, $rows);
        $this->assertNotContains('ADRIA', array_column($rows, 'brand'));
    }
}
