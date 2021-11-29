<?php

namespace App\Db\Entity;

/**
 * CallbackMessage
 *
 * @property int $id
 * @property string $name
 * @property int $phone
 * @property string $message
 */

class CallbackMessage extends ExtendedBaseEntity
{
    protected $fillable = [
        'id',
        'name',
        'phone',
        'message'
    ];

    protected $visible = [
        'id',
        'name',
        'phone',
        'message'
    ];
}
