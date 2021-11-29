<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Packet
 *
 * @property int $id
 * @property string $name
 * @property float $price
 * @property string $url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Packet extends BaseEntity
{
    protected $fillable = ['name', 'price', 'url'];

    protected $visible = [
        'id',
        'name',
        'price',
        'url'
    ];
}
