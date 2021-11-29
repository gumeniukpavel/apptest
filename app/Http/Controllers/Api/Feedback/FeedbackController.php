<?php

namespace App\Http\Controllers\Api\Feedback;

use App\Db\Entity\CallbackMessage;
use App\Db\Entity\FeedbackMessage;
use App\Db\Entity\Role;
use App\Db\Entity\User;
use App\Db\Service\CallbackDao;
use App\Db\Service\FeedbackMessageDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Feedback\CallbackMessageRequest;
use App\Http\Requests\Feedback\FeedbackMessageRequest;
use App\Notifications\FeedbackMessage\CallbackNotification;
use App\Notifications\FeedbackMessage\FeedbackMessageNotification;
use App\Service\AuthService;
use Illuminate\Support\Facades\Notification;

class FeedbackController extends BaseController
{
    protected FeedbackMessageDao $feedbackMessageDao;
    protected CallbackDao $callbackDao;

    public function __construct(
        AuthService $authService,
        FeedbackMessageDao $feedbackMessageDao,
        CallbackDao $callbackDao
    )
    {
        parent::__construct($authService);
        $this->feedbackMessageDao = $feedbackMessageDao;
        $this->callbackDao = $callbackDao;
    }

    public function actionSendFeedbackMessage(FeedbackMessageRequest $request)
    {
        /** @var User [] $users */
        $users = User::query()->where('role_id', Role::ROLE_ADMIN)->get();
        $feedbackMessage = $this->feedbackMessageDao->createFeedbackMessage(
            $request
        );

        Notification::send($users, new FeedbackMessageNotification($feedbackMessage));

        return $this->json(trans('feedback.successfullySent'));
    }

    public function actionSendCallbackMessage(CallbackMessageRequest $request)
    {
        /** @var User [] $users */
        $users = User::query()->where('role_id', Role::ROLE_ADMIN)->get();
        $callback = $this->callbackDao->createCallbackMessage(
            $request
        );

        Notification::send($users, new CallbackNotification($callback));

        return $this->json(trans('feedback.successfullySent'));
    }
}
