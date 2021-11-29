<?php

namespace App\Db\Service;

use App\Db\Entity\MediaFile;
use App\Db\Entity\Question;
use App\Db\Entity\QuestionMediaFile;

class QuestionMediaFileDao
{
    public function save(Question $question, MediaFile $mediaFile) : ?QuestionMediaFile
    {
        $questionMediaFile = new QuestionMediaFile();
        $questionMediaFile->question_id = $question->id;
        $questionMediaFile->media_file_id = $mediaFile->id;
        $questionMediaFile->save();

        return $questionMediaFile;
    }

    public function deleteMediaFiles(Question $question)
    {
        QuestionMediaFile::query()
            ->where('question_id', $question->id)
            ->delete();
    }
}
