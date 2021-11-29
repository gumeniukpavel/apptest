<?php

namespace App\Db\Entity;

/**
 * Level
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 *
 * @property Project[] $projects
 * @property Candidate[] $candidates
 * @property User $user
 */
class Tag extends BaseEntity
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
        'projects',
        'candidates'
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function candidates()
    {
        return $this->belongsToMany(Candidate::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
