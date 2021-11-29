<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Partner
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $address
 * @property string $website
 * @property string $email
 * @property string $phone
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Partner extends BaseEntity
{
    protected $fillable = ['user_id', 'title', 'address', 'website', 'email', 'phone'];

    protected $visible = [
        'id',
        'title',
        'address',
        'website',
        'email',
        'phone'
    ];
}
