<?php

declare(strict_types=1);

namespace App\Models;

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
    protected $guarded = ['id'];
}
