<?php

namespace App\Http\Controllers\Api\LetterTemplate;

use App\Db\Entity\LetterTemplate;
use App\Db\Service\LetterTemplateDao;
use App\Http\Requests\LetterTemplate\CreateLetterTemplateRequest;
use App\Http\Controllers\BaseController;
use App\Http\Requests\LetterTemplate\DeleteRequest;
use App\Http\Requests\LetterTemplate\GetListRequest;
use App\Http\Requests\LetterTemplate\SendDemoTemplateRequest;
use App\Http\Requests\LetterTemplate\SetActiveLetterTemplateRequest;
use App\Http\Requests\LetterTemplate\UpdateRequest;
use App\Http\Resources\PaginationResource;
use App\Notifications\User\DemoTemplateNotification;
use App\Service\AuthService;
use Illuminate\Support\Facades\Log;

class LetterTemplateController extends BaseController
{
    /** @var LetterTemplateDao $letterTemplateDao */
    protected $letterTemplateDao;

    public function __construct(
        AuthService $authService,
        LetterTemplateDao $letterTemplateDao
    )
    {
        parent::__construct($authService);
        $this->letterTemplateDao = $letterTemplateDao;
    }

    public function actionSaveEmailTemplate(CreateLetterTemplateRequest $request)
    {
        $user = $this->authService->getUser();
        $saveEmailTemplate = $this->letterTemplateDao->saveLetterTemplate($user, $request);

        return $this->json($saveEmailTemplate);
    }

    public function actionSendDemoTemplate(SendDemoTemplateRequest $request)
    {
        $letterTemplate = $this->letterTemplateDao->getOne($request->id);
        if (!$letterTemplate || $this->user()->cannot('view', $letterTemplate)) {
            return $this->responsePermissionsDenied();
        }

        $letterTemplate->user->notify(new DemoTemplateNotification($letterTemplate));

        return $this->json($letterTemplate);
    }

    public function actionList(GetListRequest $request)
    {
        $user = $this->authService->getUser();
        $query = $this->letterTemplateDao->getLetterTemplatesList($user);

        return $this->json(
            new PaginationResource($query, $request->getPage())
        );
    }

    public function actionGetTemplate(int $id)
    {
        $letterTemplate = $this->letterTemplateDao->getOne($id);
        if (!$letterTemplate || $this->user()->cannot('view', $letterTemplate)) {
            return $this->responsePermissionsDenied();
        }

        return $this->json(
            $letterTemplate
        );
    }

    public function actionUpdateTemplate(UpdateRequest $request)
    {
        /** @var LetterTemplate $letterTemplate */
        $letterTemplate = $this->letterTemplateDao->getOne($request->id);
        if (!$letterTemplate || $this->user()->cannot('update', $letterTemplate)) {
            return $this->responsePermissionsDenied();
        }
        $letterTemplate = $request->updateEntity($letterTemplate);
        $letterTemplate->save();

        return $this->json(
            $letterTemplate
        );
    }

    public function actionDeleteTemplate(DeleteRequest $request)
    {
        /** @var LetterTemplate $letterTemplate */
        $letterTemplate = $this->letterTemplateDao->getOne($request->id);
        if (!$letterTemplate || $this->user()->cannot('delete', $letterTemplate)) {
            return $this->responsePermissionsDenied();
        }
        $letterTemplate->delete();

        return $this->json();
    }

    public function actionSetActiveLetterTemplate(SetActiveLetterTemplateRequest $request)
    {
        $user = $this->authService->getUser();
        $template = $this->letterTemplateDao->getOne($request->id);
        if (!$user || $user->cannot('update', $template))
        {
            return $this->jsonError();
        }

        $template = $this->letterTemplateDao->setLetterTemplateStatus($template, $request->isActive, $user);

        return $this->json($template);
    }
}
