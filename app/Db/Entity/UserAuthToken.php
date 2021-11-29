<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * UserAuthTokenFactory
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 *
 * @property User $user
 */
class UserAuthToken extends BaseEntity
{
    use HasFactory;
    protected $fillable = [];

    protected $visible = [
        'id',
        'token',
        'user',
    ];

    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
