<?php

namespace App\Db\Entity\PublicEntity;

use App\Db\Entity\MediaFile;
use App\Db\Entity\Question;
use App\Db\Entity\QuestionMediaFile;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property PublicQuestionAnswer[] | Collection $answers
 * @property MediaFile[] $images
 * @property MediaFile[] $videos
 * @property MediaFile[] $audios
 */
class PublicQuestion extends Question
{
    protected $visible = [
        'id',
        'question',
        'description',
        'imageurl',
        'video_url',
        'audio_url',
        'is_free_entry',

        'answers',
        'images',
        'audios',
        'videos',
        'answers_count'
    ];

    public function answers()
    {
        return $this->hasMany(PublicQuestionAnswer::class, 'question_id', 'id');
    }

    public function audios()
    {
        return $this->hasMany(QuestionMediaFile::class, 'question_id')
            ->with('mediaFile')
            ->whereHas('mediaFile', function($mediaFile){
                $mediaFile->where('type', MediaFile::TYPE_AUDIO);
            });
    }

    public function videos()
    {
        return $this->hasMany(QuestionMediaFile::class, 'question_id')
            ->with('mediaFile')
            ->whereHas('mediaFile', function($mediaFile){
                $mediaFile->where('type', MediaFile::TYPE_VIDEO);
            });
    }

    public function images()
    {
        return $this->hasMany(QuestionMediaFile::class, 'question_id')
            ->with('mediaFile')
            ->whereHas('mediaFile', function($mediaFile){
                $mediaFile->where('type', MediaFile::TYPE_IMAGE);
            });
    }
}
