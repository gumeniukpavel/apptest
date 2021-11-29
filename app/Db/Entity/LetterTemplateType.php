<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * LetterTemplateType
 *
 * @property int $id
 * @property string $name
 */
class LetterTemplateType extends BaseEntity
{
    use HasFactory;

    const TestInvitation = 1;
    const PositiveAnswer = 2;
    const NegativeAnswer = 3;
    const TestIsCompleted = 4;
    const InterviewInvitation = 5;
    const QuestionnaireInvitation = 6;
    const QuestionnaireIsCompleted = 7;
    const ExpertInterviewInvitation = 8;

    protected $fillable = [
        'id',
        'name'
    ];

    protected $visible = [
        'id',
        'name'
    ];

    public function letterTemplate()
    {
        return $this->belongsToMany(LetterTemplate::class, 'type_id');
    }

    public static function createNew(string $name) : LetterTemplateType
    {
        $model = new self();
        $model->name = $name;
        $model->save();
        return $model;
    }
}
