<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * ErrorType
 *
 * @property int $id
 * @property string $name
 * @property Carbon $quid_created_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ErrorType extends BaseEntity
{
    protected $fillable = ['name'];

    protected $visible = [
        'id',
        'name'
    ];
}
