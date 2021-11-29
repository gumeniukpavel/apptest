<?php

namespace App\Http\Controllers\Api\Question;

use App\Db\Entity\Event;
use App\Db\Entity\MediaFile;
use App\Db\Entity\Question;
use App\Db\Entity\QuestionMediaFile;
use App\Db\Service\EventDao;
use App\Db\Service\QuestionDao;
use App\Db\Service\QuestionMediaFileDao;
use App\Db\Service\TestDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IdRequest;
use App\Http\Requests\Question\AddRequest;
use App\Http\Requests\Question\GetListRequest;
use App\Http\Requests\Question\UpdateRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;

class QuestionController extends BaseController
{
    private QuestionDao $questionDao;
    private QuestionMediaFileDao $questionMediaFileDao;
    protected TestDao $testDao;
    protected EventDao $eventService;

    public function __construct(
        QuestionDao $questionDao,
        QuestionMediaFileDao $questionMediaFileDao,
        EventDao $eventService,
        TestDao $testDao,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->questionDao = $questionDao;
        $this->questionMediaFileDao = $questionMediaFileDao;
        $this->testDao = $testDao;
        $this->eventService = $eventService;
    }

    public function getList(GetListRequest $request)
    {
        $test = $this->testDao->firstWithData($request->testId);
        if ($this->user()->cannot('view', $test)) {
            return $this->responsePermissionsDenied();
        }
        return $this->json(
            new PaginationResource(
                $this->questionDao->getPaginationQuery($request->testId),
                $request->page
            )
        );
    }

    public function getOne(int $id)
    {
        $question = $this->questionDao->getOneWithData($id);
        if ($this->user()->cannot('view', $question)) {
            return $this->responsePermissionsDenied();
        }
        return $this->json($question);
    }

    public function add(AddRequest $request)
    {
        $test = $this->testDao->getOne($request->testId);
        if ($this->user()->cannot('update', $test) || $this->user()->cannot('create', Question::class)) {
            return $this->responsePermissionsDenied();
        }
        $question = $this->questionDao->addNewFromRequest($request, $test, $this->user());

        if ($request->mediaFileIds)
        {
            foreach ($request->mediaFileIds as $mediaFileId)
            {
                $mediaFile = $this->saveMediaFile($question, $mediaFileId);

                if (!$mediaFile instanceof QuestionMediaFile)
                {
                    $question->delete();
                    return $mediaFile;
                }
            }
        }

        return $this->json(
            $question
        );
    }

    public function update(UpdateRequest $request)
    {
        $question = $this->questionDao->getOne($request->id);
        if ($this->user()->cannot('update', $question)) {
            return $this->responsePermissionsDenied();
        }
        if ($request->mediaFileIds && count($request->mediaFileIds) > 0)
        {
            $this->questionMediaFileDao->deleteMediaFiles($question);
            foreach ($request->mediaFileIds as $mediaFileId)
            {
                $mediaFile = $this->saveMediaFile($question, $mediaFileId);

                if (!$mediaFile instanceof QuestionMediaFile)
                {
                    return $mediaFile;
                }
            }
        }
        else
        {
            $this->questionMediaFileDao->deleteMediaFiles($question);
        }

        $this->eventService->createEvent(Event::EVENT_TYPE_QUESTION, Event::EVENT_SUB_TYPE_UPDATE, $this->user()->id, $question->id, $question->test_id);
        $this->questionDao->updateFromRequest($request, $question);
        return $this->json();
    }

    public function delete(IdRequest $request)
    {
        $question = $this->questionDao->getOne($request->id);
        if ($this->user()->cannot('delete', $question)) {
            return $this->responsePermissionsDenied();
        }
        $this->eventService->createEvent(Event::EVENT_TYPE_QUESTION, Event::EVENT_SUB_TYPE_DELETE, $this->user()->id, $question->id, $question->test_id);
        $this->questionDao->deleteQuestion($question);
        return $this->json();
    }

    private function saveMediaFile(Question $question, $mediaFileId)
    {
        /** @var MediaFile $mediaFile */
        $mediaFile = MediaFile::query()->where('id', $mediaFileId)->first();
        if (!$mediaFile || $this->user()->cannot('view', $mediaFile)
        ) {
            return $this->responsePermissionsDenied();
        }

        switch ($mediaFile->type)
        {
            case MediaFile::TYPE_AUDIO:
                $this->eventService->createEvent(Event::EVENT_TYPE_QUESTION, Event::EVENT_SUB_TYPE_UPLOAD_AUDIO, $this->user()->id, $question->id);
                break;

            case MediaFile::TYPE_VIDEO:
                $this->eventService->createEvent(Event::EVENT_TYPE_QUESTION, Event::EVENT_SUB_TYPE_UPLOAD_VIDEO, $this->user()->id, $question->id);
                break;

            case MediaFile::TYPE_IMAGE:
                $this->eventService->createEvent(Event::EVENT_TYPE_QUESTION, Event::EVENT_SUB_TYPE_UPLOAD_IMAGE, $this->user()->id, $question->id);
                break;
        }

        return $this->questionMediaFileDao->save($question, $mediaFile);
    }
}
