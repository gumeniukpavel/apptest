<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Customer
 *
 * @property int $id
 * @property int $user_id
 * @property int $parent_id
 * @property string $type
 * @property string $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Faq extends BaseEntity
{
    protected $fillable = ['user_id', 'parent_id', 'content', 'type'];

    protected $visible = [
        'id',
        'type',
        'content'
    ];
}
