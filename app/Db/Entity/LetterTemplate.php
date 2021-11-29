<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * LetterTemplate
 *
 * @property int $id
 * @property int $user_id
 * @property string $type_id
 * @property string $subject
 * @property string $body
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User $user
 */

class LetterTemplate extends BaseEntity
{
    protected $fillable = [
        'type_id',
        'subject',
        'body',
        'is_active'
    ];

    protected $visible = [
        'id',
        'type_id',
        'subject',
        'body',
        'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function letterTemplateType()
    {
        return $this->hasOne(LetterTemplateType::class, 'id', 'type_id');
    }
}
