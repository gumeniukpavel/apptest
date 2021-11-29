<?php

namespace App\Db\Entity;

use Carbon\Carbon;

/**
 * SystemSetting
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SystemSetting extends BaseEntity
{
    protected $fillable = ['name', 'value'];

    protected $visible = [
        'id',
        'name',
        'value'
    ];
}
