<?php

namespace App\Db\Service;

use App\Db\Entity\ExpertFile;
use App\Db\Entity\User;
use Illuminate\Http\UploadedFile;

class ExpertImageDao
{
    public function createNew(
        UploadedFile $uploadedFile,
        User $user
    ) : ExpertFile
    {
        $entity = new ExpertFile();
        $entity->name = $uploadedFile->getClientOriginalName();
        $entity->mime_type = $uploadedFile->getMimeType();
        $entity->size = $uploadedFile->getSize();
        $entity->user_id = $user->id;
        return $entity;
    }
}
