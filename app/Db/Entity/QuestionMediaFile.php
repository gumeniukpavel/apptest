<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * QuestionMediaFile
 *
 * @property int $id
 * @property int $question_id
 * @property int $media_file_id
 *
 * @property Question $question
 * @property MediaFile $mediaFile
 */
class QuestionMediaFile extends BaseEntity
{
    protected $table = 'question_media_files';

    public $timestamps = false;

    protected $visible = [
        'id',
        'question',
        'mediaFile',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function mediaFile()
    {
        return $this->belongsTo(MediaFile::class);
    }
}
