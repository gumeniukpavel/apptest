<?php

namespace App\Db\Entity;

use App\Constant\CandidateType;
use App\Constant\TestType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * Candidate
 *
 * @property int $id
 * @property int $customer_id
 * @property int $staff_level_id
 * @property int $staff_specialization_id
 * @property string $name
 * @property string $middle_name
 * @property int $image_id
 * @property string $surname
 * @property string $email
 * @property string $phone
 * @property int $career_start_year
 * @property int $specialty_work_start_year
 * @property int $company_position_id
 * @property integer $hired_at
 * @property integer $fired_at
 * @property Carbon $birth_date
 * @property string $notes
 * @property string $pdf_data_url
 * @property bool $is_invitation_sent
 * @property bool $is_last_send_error
 * @property integer $last_notification_at
 * @property integer $last_send_error_at
 *
 * @property CandidateType $type
 * @property int $test_results_count
 * @property int $yearsOld
 * @property array $tagNames
 * @property bool $cantSendNotification
 * @property Carbon|null $lastNotificationTime
 *
 * @property User $customer
 * @property StaffLevel $staffLevel
 * @property StaffSpecialization $staffSpecialization
 * @property CompanyPosition $companyPosition
 * @property CandidateFile $image
 * @property CandidateFile[] $documents
 * @property TestResult[] | Collection $testResults
 * @property TestResult[] | Collection $questionnaireResults
 * @property Project[] | Collection $projects
 * @property UserEvent[] | Collection $userEvent
 */
class Candidate extends ExtendedBaseEntity
{
    use Notifiable;

    protected $dates = [
        'birth_date'
    ];

    protected $fillable = [];

    protected $appends = [
        'yearsOld',
        'tagNames',
        'lastNotificationTime',
        'cantSendNotification',
    ];

    protected $visible = [
        'id',
        'type',
        'name',
        'middle_name',
        'surname',
        'email',
        'phone',
        'career_start_year',
        'specialty_work_start_year',
        'company_position_id',
        'hired_at',
        'fired_at',
        'birth_date',
        'notes',
        'pdf_data_url',
        'is_invitation_sent',
        'is_last_send_error',
        'last_notification_at',
        'last_send_error_at',

        'yearsOld',
        'test_results_count',
        'questionnaire_results_count',
        'user_event_count',
        'tagNames',
        'lastNotificationTime',
        'cantSendNotification',
        'isFavorite',
        'isFavoriteProjectNames',

        'customer',
        'testResults',
        'questionnaireResults',
        'projects',
        'image',
        'documents',
        'staffLevel',
        'staffSpecialization',
        'companyPosition'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function testResults()
    {
        return $this->hasManyThrough(TestResult::class, ProjectCandidate::class)
            ->whereHas('test', function($test){
                $test->where('type', TestType::$Test->getValue());
            });
    }

    public function questionnaireResults()
    {
        return $this->hasManyThrough(TestResult::class, ProjectCandidate::class)
            ->whereHas('test', function($test){
                $test->where('type', TestType::$Questionnaire->getValue());
            });
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function userEvent()
    {
        return $this->hasMany(UserEvent::class);
    }

    public function image()
    {
        return $this->belongsTo(CandidateFile::class, 'image_id');
    }

    public function documents()
    {
        return $this->hasMany(CandidateFile::class)
            ->where('type', CandidateFile::TYPE_DOCUMENT);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'candidate_tags')->select(['name']);
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

    public function companyPosition(): BelongsTo
    {
        return $this->belongsTo(CompanyPosition::class);
    }

    public function getYearsOldAttribute()
    {
        if (!$this->birth_date)
        {
            return 0;
        }
        return Carbon::now()->diffInYears($this->birth_date);
    }

    public function getTypeAttribute()
    {
        return CandidateType::getEnumObject($this->attributes['type']);
    }

    public function setTypeAttribute(CandidateType $type)
    {
        $this->attributes['type'] = $type->getValue();
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
