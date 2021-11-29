<?php

namespace App\Db\Entity;

/**
 * Country
 *
 * @property int $id
 * @property string $name
 */

class Country extends BaseEntity
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name'
    ];

    protected $visible = [
        'id',
        'name'
    ];
}
