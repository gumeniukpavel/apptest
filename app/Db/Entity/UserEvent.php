<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * UserEvent
 *
 * @property int $id
 * @property int $user_id
 * @property int $candidate_id
 * @property int $start_date
 * @property int $end_date
 * @property string $description
 * @property string $status
 * @property string $access_token_yes
 * @property string $access_token_no
 * @property int $created_at
 *
 * @property User $user
 * @property Candidate $candidate
 * @property ExpertInterviewEvent $expertInterviewEvent
 */
class UserEvent extends BaseEntity
{
    use HasFactory;

    const USER_EVENT_STATUS_PENDING = 'PENDING';
    const USER_EVENT_STATUS_APPROVED = 'APPROVED';
    const USER_EVENT_STATUS_CANCELED = 'CANCELED';

    const USER_EVENT_STATUSES = [
        self::USER_EVENT_STATUS_PENDING,
        self::USER_EVENT_STATUS_APPROVED,
        self::USER_EVENT_STATUS_CANCELED,
    ];

    protected $fillable = [
        'user_id',
        'candidate_id',
        'start_date',
        'end_date',
        'description',
        'status',
        'access_token_yes',
        'access_token_no'
    ];

    protected $visible = [
        'id',
        'user_id',
        'candidate_id',
        'start_date',
        'end_date',
        'description',
        'status',

        'user',
        'candidate',
        'expertInterviewEvent'
    ];

    protected $dateFormat = 'U';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function expertInterviewEvent()
    {
        return $this->hasMany(ExpertInterviewEvent::class, 'user_event_id');
    }
}
