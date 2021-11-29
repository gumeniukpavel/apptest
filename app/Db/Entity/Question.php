<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Collection;

/**
 * @property int $id
 * @property int $category_id
 * @property int $level_id
 * @property int $user_id
 * @property int $test_id
 * @property string $description
 * @property int $score
 * @property boolean $is_free_entry
 *
 * @property User $user
 * @property Category $category
 * @property MediaFile[] $images
 * @property MediaFile[] $videos
 * @property MediaFile[] $audios
 * @property QuestionAnswer[] | Collection $answers
 * @property Test $test
 * @property TestResultAnswer[] | Collection $testResultAnswers
 */
class Question extends ExtendedBaseEntity
{
    protected $table = 'questions';

    protected $fillable = ['category_id', 'question', 'description'];

    protected $visible = [
        'id',
        'question',
        'description',
        'score',
        'is_free_entry',

        'answers',
        'images',
        'audios',
        'videos',
        'test',
        'testResultAnswers',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(QuestionAnswer::class, 'question_id', 'id');
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function testResultAnswers()
    {
        return $this->hasMany(TestResultAnswer::class);
    }

    public function audios()
    {
        return $this->hasMany(QuestionMediaFile::class)
            ->with('mediaFile')
            ->whereHas('mediaFile', function($mediaFile){
                $mediaFile->where('type', MediaFile::TYPE_AUDIO);
            });
    }

    public function videos()
    {
        return $this->hasMany(QuestionMediaFile::class)
            ->with('mediaFile')
            ->whereHas('mediaFile', function($mediaFile){
                $mediaFile->where('type', MediaFile::TYPE_VIDEO);
            });
    }

    public function images()
    {
        return $this->hasMany(QuestionMediaFile::class)
            ->with('mediaFile')
            ->whereHas('mediaFile', function($mediaFile){
                $mediaFile->where('type', MediaFile::TYPE_IMAGE);
            });
    }
}
