<?php

namespace App\Http\Controllers\Api;

use App\Db\Entity\Event;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use App\Db\Entity\TestResult;
use App\Db\Service\EventDao;
use App\Db\Service\QuestionAnswerDao;
use App\Db\Service\QuestionDao;
use App\Db\Service\TestResultAnswerDao;
use App\Db\Service\TestResultDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Test\SaveAnswer;
use App\Notifications\User\QuestionnaireCompletedNotification;
use App\Notifications\Candidate\QuestionnaireResultNotification;
use App\Service\AuthService;
use Carbon\Carbon;

class UserQuestioningController extends BaseController
{
    protected TestResultDao $testResultService;
    protected TestResultAnswerDao $testResultAnswerService;
    protected QuestionDao $questionService;
    protected QuestionAnswerDao $questionAnswerService;

    protected EventDao $eventService;

    public function __construct(
        TestResultDao $testResultDao,
        TestResultAnswerDao $testResultAnswerDao,
        QuestionDao $questionDao,
        QuestionAnswerDao $questionAnswerDao,
        EventDao $eventService,
        AuthService $authService)
    {
        parent::__construct($authService);
        $this->testResultService = $testResultDao;
        $this->testResultAnswerService = $testResultAnswerDao;
        $this->questionService = $questionDao;
        $this->questionAnswerService = $questionAnswerDao;
        $this->eventService = $eventService;
    }

    public function actionGetTestInfo(string $token)
    {
        $testResult = $this->testResultService->getPublicByToken($token);

        if (!$testResult)
        {
            return $this->responseNotFound(trans('questionnaires.questionnaireNotFound'));
        }
        else
        {
            if ($testResult->test->isTest())
            {
                return $this->jsonError();
            }

            return $this->json(
                $this->testResultService->getPublicByToken($token)
            );
        }
    }

    public function actionStartTest(string $token)
    {
        $testResult = $this->testResultService->getPublicByToken($token);

        if (!$testResult)
        {
            return $this->responseNotFound(trans('questionnaires.questionnaireNotFound'));
        }
        else
        {
            if ($testResult->test->isTest())
            {
                return $this->jsonError();
            }

            if ($testResult->started_at)
            {
                return $this->jsonError(trans('questionnaires.questionnaireAlreadyStarted'));
            }
            else
            {
                $testResult->started_at = Carbon::now();
                $testResult->save();

                /** @var Event $event */
                $event = $this->eventService->createEvent(
                    Event::EVENT_TYPE_TESTING,
                    Event::EVENT_SUB_TYPE_START_QUESTIONNAIRE,
                    $testResult->projectCandidate->candidate->customer_id,
                    $testResult->test_id,
                    $testResult->project_candidate_id,
                    $testResult->id
                );

                $event->is_popup_notification = true;
                $event->save();

                return $this->json($testResult);
            }
        }
    }

    public function actionGetNextQuestion(string $token)
    {
        $testResult = $this->testResultService->getPublicByToken($token);

        if ($testResult->test->isTest())
        {
            return $this->jsonError();
        }

        if (!$testResult->started_at)
        {
            return $this->jsonError(trans('questionnaires.questionnaireNotStarted'));
        }

        $testNextQuestion = $this->testResultAnswerService->getNextQuestionWithData($testResult->id);
        if (!$testNextQuestion)
        {
            return $this->jsonError(trans('questionnaires.nextQuestionNotFound'));
        }
        else
        {
            $testNextQuestion->started_at = Carbon::now();
            $testNextQuestion->save();

            return $this->json(
                [
                    'question' => $testNextQuestion->question,
                    'testInfo' => $testResult
                ]
            );
        }
    }

    public function actionSaveAnswer(string $token, SaveAnswer $request)
    {
        $testResult = $this->testResultService->getOneByToken($token);

        if ($testResult->test->isTest())
        {
            return $this->jsonError();
        }

        if (!$testResult->started_at)
        {
            return $this->jsonError(trans('questionnaires.questionnaireNotStarted'));
        }

        $testCurrentQuestion = $this->testResultAnswerService->getNextQuestionWithData($testResult->id);
        if (!$testCurrentQuestion)
        {
            return $this->responseNotFound(trans('questionnaires.questionNotFound'));
        }
        else
        {
            if ($request->answerId)
            {
                $question = $testCurrentQuestion->question;
                $answer = $this->questionAnswerService->getOne($question->id, $request->answerId);

                if (!$answer)
                {
                    return $this->responseNotFound(trans('questionnaires.answerNotFound'));
                }
                else
                {
                    $testCurrentQuestion->result_value = 0;
                    $testCurrentQuestion->question_answer_id = $answer->id;
                    $testCurrentQuestion->time_for = Carbon::now()->diffInSeconds(
                        $testCurrentQuestion->started_at
                    );
                    $testCurrentQuestion->save();
                }
            }
            else
            {
                $testCurrentQuestion->result_value = 0;
                $testCurrentQuestion->custom_answer = $request->customAnswer;
                $testCurrentQuestion->time_for = Carbon::now()->diffInSeconds(
                    $testCurrentQuestion->started_at
                );
                $testCurrentQuestion->save();
            }

            $nextQuestion = $this->testResultAnswerService->getNextQuestionWithData($testResult->id);
            if (!$nextQuestion)
            {
                $user = $testResult->test->user;
                $this->updateTestState($testResult);
                $userProfile = $testResult->test->user->profile;
                if ($user)
                {
                    /** @var LetterTemplate $template */
                    $template = LetterTemplate::query()
                        ->where([
                            'user_id' => $user->id,
                            'type_id' => LetterTemplateType::QuestionnaireIsCompleted,
                            'is_active' => true
                        ])->first();
                }
                else{
                    $template = null;
                }

                $testResult->projectCandidate->candidate
                    ->notify(new QuestionnaireResultNotification(
                        $testResult,
                        $userProfile,
                        $template
                    ));

                if ($user)
                {
                    if ($user->is_send_email_test_completed_notification)
                    {
                        $user->notify(new QuestionnaireCompletedNotification($testResult, $user->name, $user->middle_name));
                    }
                }

                $this->updateTestState($testResult);

                return $this->json(
                    $this->testResultService->getPublicByToken($token)
                );
            }

            return $this->json();
        }
    }

    public function updateTestState(TestResult $testResult)
    {
        $testResult->finished_at = Carbon::now();
        $testResult->is_passed = true;
        $testResult->save();

        /** @var Event $event */
        $event = $this->eventService->createEvent(
            Event::EVENT_TYPE_TESTING,
            Event::EVENT_SUB_TYPE_END_QUESTIONNAIRE,
            $testResult->projectCandidate->candidate->customer_id,
            $testResult->test_id,
            $testResult->project_candidate_id,
            $testResult->id
        );

        $event->is_popup_notification = true;
        $event->save();
    }
}
