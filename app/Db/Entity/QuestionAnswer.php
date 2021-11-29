<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * QuestionAnswer
 *
 * @property int $id
 * @property int $question_id
 * @property string $answer
 * @property boolean $is_right
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Question $question
 * @property TestResultAnswer[] | Collection $testCandidateQuestions
 * @property Category[] | Collection $categories
 */
class QuestionAnswer extends ExtendedBaseEntity
{
    protected $table = 'question_answers';

    protected $fillable = [
        'question_id',
        'answer',
        'is_right'
    ];

    protected $visible = [
        'id',
        'answer',
        'is_right',
        'question',
        'testCandidateQuestions',
        'categories'
    ];

    public function testCandidateQuestions()
    {
        return $this->hasMany(TestResultAnswer::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'id', 'question_id');
    }

    public function categories()
    {
        return $this->belongsTo(Category::class, 'questions', 'category_id', 'id');
    }

}
