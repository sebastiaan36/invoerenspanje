<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row of Anexo I of the yearly Orden HAC "precios medios de venta":
 * the value Hacienda assigns to a model in its first year after registration.
 *
 * @property int $id
 * @property int $table_year
 * @property string $brand_key
 * @property string $brand
 * @property string $model
 * @property int $start_year
 * @property int|null $end_year
 * @property int|null $cc
 * @property string $fuel
 * @property int $kw
 * @property int|null $cv
 * @property int $price_eur
 */
final class HaciendaVehiclePrice extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * Uppercase alphanumerics only, so "Mercedes-Benz" and "MERCEDES BENZ" compare equal.
     */
    public static function normalizeKey(string $value): string
    {
        return (string) preg_replace('/[^A-Z0-9]/', '', strtoupper(self::stripAccents($value)));
    }

    public static function stripAccents(string $value): string
    {
        return strtr($value, [
            'Á' => 'A', 'À' => 'A', 'Ä' => 'A', 'É' => 'E', 'È' => 'E', 'Ë' => 'E', 'Í' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ö' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N', 'Ç' => 'C',
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ë' => 'e', 'í' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ö' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n', 'ç' => 'c',
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'table_year' => 'integer',
            'start_year' => 'integer',
            'end_year' => 'integer',
            'cc' => 'integer',
            'kw' => 'integer',
            'cv' => 'integer',
            'price_eur' => 'integer',
        ];
    }
}
