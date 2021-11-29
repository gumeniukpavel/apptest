<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Collection;

/**
 * StaffLevel
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 *
 * @property Candidate[] | Collection $candidates
 * @property Expert[] | Collection $experts
 * @property User $user
 */
class StaffLevel extends BaseEntity
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'user_id'
    ];

    protected $visible = [
        'id',
        'name',

        'user',
        'candidates',
        'experts'
    ];

    public function candidates()
    {
        return $this->belongsToMany(Candidate::class);
    }

    public function experts()
    {
        return $this->belongsToMany(Expert::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
