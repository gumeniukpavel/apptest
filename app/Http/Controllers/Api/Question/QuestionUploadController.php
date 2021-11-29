<?php

namespace App\Http\Controllers\Api\Question;

use App\Db\Entity\MediaFile;
use App\Db\Service\EventDao;
use App\Db\Service\MediaFileDao;
use App\Db\Service\QuestionDao;
use App\Db\Service\QuestionMediaFileDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Question\UploadAudioRequest;
use App\Http\Requests\Question\UploadImageRequest;
use App\Http\Requests\Question\UploadVideoRequest;
use App\Service\AuthService;
use App\Service\StorageService;

class QuestionUploadController extends BaseController
{
    private QuestionDao $questionDao;
    private StorageService $storageService;
    private MediaFileDao $mediaFileDao;
    private QuestionMediaFileDao $questionMediaFileDao;

    protected EventDao $eventService;

    public function __construct(
        QuestionDao $questionDao,
        EventDao $eventService,
        StorageService $storageService,
        MediaFileDao $mediaFileDao,
        QuestionMediaFileDao $questionMediaFileDao,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->questionDao = $questionDao;
        $this->storageService = $storageService;
        $this->mediaFileDao = $mediaFileDao;
        $this->questionMediaFileDao = $questionMediaFileDao;
        $this->eventService = $eventService;
    }

    public function uploadAudio(UploadAudioRequest $request)
    {
        $uploadedFile = $request->audio;
        $responseFileEntity = $this->mediaFileDao->createNew(
            $uploadedFile,
            $this->user(),
            MediaFile::TYPE_AUDIO
        );
        $fileName = $this->storageService->saveQuestionAudioFile($uploadedFile);
        $responseFileEntity->path = $fileName;
        $responseFileEntity->save();

        return $this->json(
            $responseFileEntity
        );
    }

    public function uploadVideo(UploadVideoRequest $request)
    {
        $uploadedFile = $request->video;
        $responseFileEntity = $this->mediaFileDao->createNew(
            $uploadedFile,
            $this->user(),
            MediaFile::TYPE_VIDEO
        );
        $fileName = $this->storageService->saveQuestionVideoFile($uploadedFile);
        $responseFileEntity->path = $fileName;
        $responseFileEntity->save();

        return $this->json(
            $responseFileEntity
        );
    }

    public function uploadImage(UploadImageRequest $request)
    {
        $uploadedFile = $request->image;
        $responseFileEntity = $this->mediaFileDao->createNew(
            $request->image,
            $this->user(),
            MediaFile::TYPE_IMAGE
        );
        $image = $this->storageService->saveQuestionImageFile($uploadedFile);
        $responseFileEntity->path = $image->path;
        $responseFileEntity->cropped_path = $image->pathCropped;
        $responseFileEntity->save();

        return $this->json(
            $responseFileEntity
        );
    }
}
