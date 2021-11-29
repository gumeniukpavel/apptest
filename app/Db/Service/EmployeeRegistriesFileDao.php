<?php

namespace App\Db\Service;

use App\Db\Entity\EmployeeRegistriesFile;
use App\Db\Entity\User;
use Illuminate\Http\UploadedFile;

class EmployeeRegistriesFileDao
{
    public function createNew(
        UploadedFile $uploadedFile,
        User $user
    ) : EmployeeRegistriesFile
    {
        $entity = new EmployeeRegistriesFile();
        $entity->name = $uploadedFile->getClientOriginalName();
        $entity->mime_type = $uploadedFile->getMimeType();
        $entity->size = $uploadedFile->getSize();
        $entity->user_id = $user->id;
        return $entity;
    }
}
