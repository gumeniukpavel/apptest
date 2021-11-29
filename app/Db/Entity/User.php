<?php

namespace App\Db\Entity;

use App\Db\Entity\Extensions\PrepareToArrayData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * User
 *
 * @property int $id
 * @property string $account_type
 * @property int $role_id
 * @property string $name
 * @property string $middle_name
 * @property string $bucket_id
 * @property string $secret_token
 * @property string $surname
 * @property string $email
 * @property string $avatar
 * @property Carbon $email_verified_at
 * @property string $password
 * @property string $remember_token
 * @property string $settings
 * @property string $phone
 * @property bool $is_verified
 * @property bool $is_send_email_news
 * @property bool $is_send_email_test_completed_notification
 * @property bool $is_send_email_tariff
 * @property bool $is_send_sms_news
 * @property bool $is_send_sms_test_completed_notification
 * @property bool $is_send_sms_tariff
 * @property Carbon $birth_date
 * @property bool $is_processed_personal_data
 * @property string $email_verification_token
 * @property Carbon $email_verification_token_created_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property string $apiToken
 * @property string $amazonSecretToken
 * @property string $fullName
 *
 * @property Role $role
 * @property UserProfile $profile
 * @property Candidate $candidate
 * @property Test[] | Collection $tests
 * @property TestResult[] | Collection $testCandidates
 * @property Project[] | Collection $projects
 * @property UserAuthToken[] | Collection $authTokens
 * @property TariffUser[] | Collection $tariffUser
 */
class User extends \TCG\Voyager\Models\User
{
    use Notifiable,
        PrepareToArrayData,
        SoftDeletes,
        HasFactory;

    protected $fillable = [
        'account_type',
        'name',
        'surname',
        'middle_name',
        'bucket_id',
        'secret_token',
        'email',
        'phone',
        'role_id',
        'password',
        'is_processed_personal_data',
        'is_verificated'
    ];

    protected $visible = [
        'id',
        'account_type',
        'name',
        'middle_name',
        'surname',
        'email',
        'avatar',
        'settings',
        'phone',
        'birth_date',
        'is_verified',
        'is_processed_personal_data',
        'apiToken',
        'is_send_email_news',
        'is_send_email_test_completed_notification',
        'is_send_email_tariff',
        'is_send_sms_test_completed_notification',
        'is_send_sms_tariff',
        'role',
        'profile',
        'candidate',
        'tests',
        'testCandidates',
        'projects',
        'authTokens',

        'fullName',
        'secretToken'
    ];

    protected $appends = [
        'fullName',
        'amazonSecretToken'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_token_created_at' => 'datetime',
    ];

    public function generateToken(): UserAuthToken
    {
        $newToken = new UserAuthToken();
        $newToken->token = base64_encode($this->id) . Str::random(60);
        $this->authTokens()->save($newToken);
        return $newToken;
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function candidate()
    {
        return $this->hasOne(Candidate::class)->with('candidateLevel');
    }

    public function tests()
    {
        return $this->belongsToMany(Test::class, 'test_candidates', 'user_id', 'test_id');
    }

    public function testCandidates()
    {
        return $this->hasOne(TestResult::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function tariffUser()
    {
        return $this->hasMany(TariffUser::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function authTokens()
    {
        return $this->hasOne(UserAuthToken::class);
    }

    #region isRole
    public function isAdmin(): bool
    {
        return $this->role->name == Role::ROLE_NAME_ADMIN;
    }

    public function isCustomer(): bool
    {
        return $this->role->name == Role::ROLE_NAME_CUSTOMER;
    }

    public function isCandidate(): bool
    {
        return $this->role->name == Role::ROLE_NAME_CANDIDATE;
    }

    public function isBookkeeper(): bool
    {
        return $this->role->name == Role::ROLE_NAME_BOOKKEEPER;
    }
    #endregion

    public function setSettingsAttribute($value)
    {
        $this->attributes['settings'] = $value ? $value->toJson() : null;
    }

    public function setAvatarAttribute($value)
    {
        $data = json_decode($value);
        $this->attributes['avatar'] = $data ? $data[0]->download_link : $value;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function setSecretTokenAttribute($value)
    {
        $this->attributes['secret_token'] = Crypt::encryptString($value);
    }

    public function getAmazonSecretTokenAttribute()
    {
        return Crypt::decryptString($this->secret_token);
    }

    public function getApiTokenAttribute(): ?string
    {
        /** @var UserAuthToken $authToken */
        $authToken = $this->authTokens()->first();
        return $authToken ? $authToken->token : null;
    }

    public function getFullNameAttribute()
    {
        return "$this->surname $this->name $this->middle_name";
    }
}
