<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * Expert
 *
 * @property int $id
 * @property int $customer_id
 * @property int $staff_level_id
 * @property int $staff_specialization_id
 * @property string $name
 * @property string $middle_name
 * @property string $surname
 * @property int $image_id
 * @property string $email
 * @property string $phone
 * @property int $career_start_year
 * @property int $specialty_work_start_year
 * @property Carbon $birth_date
 * @property string $notes
 * @property string $pdf_data_url
 * @property bool $is_invitation_sent
 * @property bool $is_last_send_error
 * @property integer $last_notification_at
 * @property integer $last_send_error_at
 *
 * @property array $tagNames
 * @property bool $cantSendNotification
 * @property Carbon|null $lastNotificationTime
 *
 * @property StaffLevel $staffLevel
 * @property StaffSpecialization $staffSpecialization
 * @property ExpertFile $image
 * @property ExpertFile[] $documents
 * @property User $customer
 * @property ExpertInterviewEvent[] | Collection $expertInterviewEvent
 */
class Expert extends BaseEntity
{
    use Notifiable;

    protected $dates = [
        'birth_date'
    ];

    protected $fillable = [];

    protected $appends = [
        'tagNames',
        'lastNotificationTime',
        'cantSendNotification',
    ];

    protected $visible = [
        'id',
        'customer_id',
        'name',
        'middle_name',
        'surname',
        'email',
        'phone',
        'career_start_year',
        'specialty_work_start_year',
        'birth_date',
        'notes',
        'pdf_data_url',
        'is_invitation_sent',
        'is_last_send_error',
        'last_notification_at',
        'last_send_error_at',

        'image',
        'expert_interview_event_count',

        'tagNames',
        'lastNotificationTime',
        'cantSendNotification',

        'documents',
        'staffLevel',
        'staffSpecialization'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function image()
    {
        return $this->belongsTo(ExpertFile::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'expert_tags')->select(['name']);
    }

    public function documents()
    {
        return $this->hasMany(ExpertFile::class)
            ->where('type', ExpertFile::TYPE_DOCUMENT);
    }

    public function ExpertInterviewEvent()
    {
        return $this->hasMany(ExpertInterviewEvent::class);
    }

    public function getTagNamesAttribute()
    {
        return $this->tags()->pluck('name')->toArray();
    }

    public function staffLevel(): BelongsTo
    {
        return $this->belongsTo(StaffLevel::class);
    }

    public function staffSpecialization(): BelongsTo
    {
        return $this->belongsTo(StaffSpecialization::class);
    }

    public function getCantSendNotificationAttribute(): bool
    {
        return $this->lastNotificationTime
            && $this->lastNotificationTime->greaterThan(Carbon::now()->subMinutes(10));
    }

    public function getLastNotificationTimeAttribute(): ?Carbon
    {
        if (!$this->last_notification_at)
        {
            return null;
        }
        return Carbon::parse($this->last_notification_at);
    }
}
