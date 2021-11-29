<?php

namespace App\Db\Entity;

/**
 * FeedbackMessage
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int $phone
 * @property string $message
 */

class FeedbackMessage extends ExtendedBaseEntity
{
    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'message'
    ];

    protected $visible = [
        'id',
        'name',
        'email',
        'phone',
        'message'
    ];
}
