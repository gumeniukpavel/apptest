<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ExpertInterviewEvent
 *
 * @property int $id
 * @property int $user_id
 * @property int $expert_id
 * @property int $user_event_id
 * @property string $status
 * @property string $access_token_yes
 * @property string $access_token_no
 *
 * @property User $user
 * @property Expert $expert
 * @property UserEvent $userEvent
 */
class ExpertInterviewEvent extends BaseEntity
{
    use HasFactory;

    const EXPERT_EVENT_STATUS_PENDING = 'PENDING';
    const EXPERT_EVENT_STATUS_APPROVED = 'APPROVED';
    const EXPERT_EVENT_STATUS_CANCELED = 'CANCELED';

    const EXPERT_EVENT_STATUSES = [
        self::EXPERT_EVENT_STATUS_PENDING,
        self::EXPERT_EVENT_STATUS_APPROVED,
        self::EXPERT_EVENT_STATUS_CANCELED,
    ];

    protected $fillable = [
        'user_id',
        'expert_id',
        'user_event_id',
        'status',
        'access_token_yes',
        'access_token_no'
    ];

    protected $visible = [
        'id',
        'user_id',
        'expert_id',
        'user_event_id',
        'status',

        'user',
        'expert',
        'userEvent'
    ];

    protected $dateFormat = 'U';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class, 'expert_id');
    }

    public function userEvent()
    {
        return $this->belongsTo(UserEvent::class, 'user_event_id');
    }
}
