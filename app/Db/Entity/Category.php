<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Category
 *
 * @property int $id
 * @property string $name
 *
 * @property Project[] | Collection $projects
 */
class Category extends BaseEntity
{
    use HasFactory;

    const MySQL = 1;
    const CSharp = 2;
    const CPlusPlus = 3;
    const Java = 4;
    const JavaScript = 5;
    const PHP = 6;
    const DotNET = 7;
    const ProjectManagement = 8;
    const QA = 9;
    const Design = 10;
    const Marketing = 11;
    const Html_Css = 12;
    const Sales = 13;
    const Custom = 14;

    protected $fillable = [
        'name'
    ];

    protected $visible = [
        'id',
        'name',
        'projects'
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'category_id');
    }

    public static function createNew(string $name) : Category
    {
        $model = new self();
        $model->name = $name;
        $model->save();
        return $model;
    }

    public function questionAnswers()
    {
        return $this->hasManyThrough(QuestionAnswer::class, 'questions', 'question_id', 'id');
    }
}
