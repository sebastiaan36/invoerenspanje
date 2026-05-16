<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $woonplaats_spanje
 * @property string|null $expected_move_date
 * @property string|null $comment
 * @property string $kenteken
 * @property string $package_slug
 * @property bool $residency_change
 * @property string $autonomia
 * @property int|null $bpm_teruggave_indicatie_eur
 * @property int|null $import_kosten_indicatie_eur
 * @property int|null $totaalprijs_indicatie_eur
 * @property string $source
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property string|null $utm_campaign
 * @property string $status
 */
final class Lead extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'residency_change' => 'boolean',
            'bpm_teruggave_indicatie_eur' => 'integer',
            'import_kosten_indicatie_eur' => 'integer',
            'totaalprijs_indicatie_eur' => 'integer',
            'rdw_snapshot_json' => 'array',
            'bpm_calculation_json' => 'array',
            'import_calculation_json' => 'array',
        ];
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }
}
