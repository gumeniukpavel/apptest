<?php

namespace App\Db\Entity;

use App\Db\Entity\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PasswordResetToken
 *
 * @property string $email
 * @property string $token
 * @property Carbon $created_at
 */
class PasswordResetToken extends BaseEntity
{
    protected $table = 'password_resets';
    const UPDATED_AT = null;

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = ['email', 'token'];

    protected $visible = ['email', 'token'];
}
