<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ProjectTest
 *
 * @property int $id
 * @property int $project_id
 * @property int $test_id
 *
 * @property int $projectTestId;
 *
 * @property Project | Model | null $project;
 * @property Test | Model | null $test;
 * @property ProjectCandidate[] | Collection | null $projectCandidates;
 */
class ProjectTest extends BaseEntity
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'test_id'
    ];

    protected $visible = [
        'project',
        'test',
        'projectCandidates',

        'projectTestId'
    ];

    protected $appends = [
        'projectTestId'
    ];

    public $timestamps = false;

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function projectCandidates()
    {
        return $this->hasMany(ProjectCandidate::class, 'project_test_id')
            ->with('candidate');
    }

    public function getProjectTestIdAttribute()
    {
        return $this->id;
    }
}
