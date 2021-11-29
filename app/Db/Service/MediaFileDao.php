<?php

namespace App\Db\Service;

use App\Db\Entity\MediaFile;
use App\Db\Entity\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class MediaFileDao
{
    public function createNew(
        UploadedFile $uploadedFile,
        User $user,
        string $type
    ) : MediaFile
    {
        $entity = new MediaFile();
        $entity->name = $uploadedFile->getClientOriginalName();
        $entity->type = $type;
        $entity->mime_type = $uploadedFile->getMimeType();
        $entity->size = $uploadedFile->getSize();
        $entity->user_id = $user->id;
        return $entity;
    }
}
