<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ExtendedBaseEntity
 *
 * @property Carbon $deleted_at
 */
class ExtendedBaseEntity extends BaseEntity
{
    use SoftDeletes;

    protected $hidden = [
        'deleted_at'
    ];
}
