<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $kenteken
 * @property string|null $merk
 * @property string|null $model
 * @property int|null $bouwjaar
 * @property string|null $brandstof
 * @property string|null $ip_address
 * @property string|null $page
 */
final class KentekenLookup extends Model
{
    use MassPrunable;

    /** Lookups (with the visitor's IP address) are kept this many days, then pruned by `model:prune`. */
    public const int RETENTION_DAYS = 90;

    protected $guarded = ['id'];

    /**
     * @return Builder<KentekenLookup>
     */
    public function prunable(): Builder
    {
        return self::query()->where('created_at', '<', now()->subDays(self::RETENTION_DAYS));
    }
}
