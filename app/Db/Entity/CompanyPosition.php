<?php

namespace App\Db\Entity;

use App\Constant\CandidateType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * CompanyPosition
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $vacancy_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User $user
 * @property Candidate[] | Collection $candidates
 */
class CompanyPosition extends BaseEntity
{
    protected $fillable = [
        'user_id',
        'name',
        'vacancy_count'
    ];

    protected $visible = [
        'id',
        'name',
        'vacancy_count',

        'employees_count',

        'user',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employees()
    {
        return $this->hasMany(Candidate::class)->where('type', CandidateType::$Employee->getValue());
    }
}
