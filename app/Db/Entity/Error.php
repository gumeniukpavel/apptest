<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Error
 *
 * @property int $id
 * @property int $error_type_id
 * @property string $subject
 * @property string $body
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Error extends BaseEntity
{
    protected $fillable = [
        'error_type_id',
        'subject',
        'body'
    ];

    protected $visible = [
        'id',
        'subject',
        'body'
    ];
}
