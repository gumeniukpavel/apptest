<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Psy\Util\Json;

/**
 * News
 *
 * @property int $id
 * @property int $user_id
 * @property string $subject
 * @property string $content
 * @property string $imageurl
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property json $profile_data
 */
class News extends BaseEntity
{
    protected $fillable = ['user_id', 'subject', 'content', 'imageurl'];

    protected $visible = [
        'id',
        'subject',
        'content',
        'imageurl'
    ];
}
