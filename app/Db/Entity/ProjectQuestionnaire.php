<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ProjectQuestionnaire
 *
 * @property int $id
 * @property int $project_id
 * @property int $questionnaire_id
 *
 * @property int $projectQuestionnaireId
 *
 * @property Project $project;
 * @property Test $questionnaire;
 * @property ProjectCandidate[] $projectCandidates;
 */
class ProjectQuestionnaire extends BaseEntity
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'questionnaire_id'
    ];

    protected $visible = [
        'project',
        'questionnaire',
        'projectCandidates',

        'projectQuestionnaireId'
    ];

    protected $appends = [
        'projectQuestionnaireId'
    ];

    public $timestamps = false;

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function questionnaire()
    {
        return $this->belongsTo(Test::class);
    }

    public function projectCandidates()
    {
        return $this->hasMany(ProjectCandidate::class, 'project_questionnaire_id')
            ->with('candidate');
    }

    public function getProjectQuestionnaireIdAttribute()
    {
        return $this->id;
    }
}
