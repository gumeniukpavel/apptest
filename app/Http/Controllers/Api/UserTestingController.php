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
use App\Notifications\User\TestCompletedNotification;
use App\Notifications\Candidate\TestResultNotification;
use App\Service\AuthService;
use Carbon\Carbon;

class UserTestingController extends BaseController
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
            return $this->responseNotFound(trans('tests.testNotFound'));
        }
        else
        {
            if ($testResult->test->isQuestionnaire())
            {
                return $this->jsonError();
            }

            if ($testResult->timeIsOver)
            {
                return $this->jsonError(trans('tests.testInNotPassed'));
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
            return $this->responseNotFound(trans('tests.testNotFound'));
        }
        else
        {
            if ($testResult->test->isQuestionnaire())
            {
                return $this->jsonError();
            }

            if ($testResult->started_at)
            {
                return $this->jsonError(trans('tests.testAlreadyStarted'));
            }
            else
            {
                $testResult->started_at = Carbon::now();
                $testResult->save();

                /** @var Event $event */
                $event = $this->eventService->createEvent(
                    Event::EVENT_TYPE_TESTING,
                    Event::EVENT_SUB_TYPE_START_TEST,
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

        if ($testResult->test->isQuestionnaire())
        {
            return $this->jsonError();
        }

        if (!$testResult->started_at)
        {
            return $this->jsonError(trans('tests.testNotStarted'));
        }

        if ($testResult->timeIsOver)
        {
            /** @var Event $event */
            $event = $this->eventService->createEvent(
                Event::EVENT_TYPE_TESTING,
                Event::EVENT_SUB_TYPE_END_TEST,
                $testResult->projectCandidate->candidate->customer_id,
                $testResult->test_id,
                $testResult->project_candidate_id,
                $testResult->id
            );

            $event->is_popup_notification = true;
            $event->save();

            return $this->jsonError(trans('tests.testTimeIsOver'));
        }

        $testNextQuestion = $this->testResultAnswerService->getNextQuestionWithData($testResult->id);
        if (!$testNextQuestion)
        {
            return $this->responseNotFound(trans('tests.nextQuestionNotFound'));
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

        if ($testResult->test->isQuestionnaire())
        {
            return $this->jsonError();
        }

        if (!$testResult->started_at)
        {
            return $this->jsonError(trans('tests.testNotStarted'));
        }

        if ($testResult->timeIsOver)
        {
            // Test time is over

            $this->updateTestState($testResult);
            return $this->jsonError(trans('tests.testTimeIsOver'));
        }

        $testCurrentQuestion = $this->testResultAnswerService->getNextQuestionWithData($testResult->id);
        if (!$testCurrentQuestion)
        {
            return $this->responseNotFound(trans('tests.questionNotFound'));
        }
        else
        {
            if ($request->answerId)
            {
                $question = $testCurrentQuestion->question;
                $answer = $this->questionAnswerService->getOne($question->id, $request->answerId);

                if (!$answer)
                {
                    return $this->responseNotFound(trans('tests.answerNotFound'));
                }
                else
                {
                    $testCurrentQuestion->result_value = $answer->is_right ? $question->score : 0;
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


                // Is Test finished
            $testCurrentQuestion = $this->testResultAnswerService->getNextQuestionWithData($testResult->id);
            if (!$testCurrentQuestion)
            {
                // Test is completed
                $this->updateTestState($testResult, true);
                $userProfile = $testResult->test->user->profile;
                $user = $testResult->test->user;

                if ($user)
                {
                    /** @var LetterTemplate $template */
                    $template = LetterTemplate::query()
                        ->where([
                            'user_id' => $user->id,
                            'type_id' => LetterTemplateType::TestIsCompleted,
                            'is_active' => true
                        ])->first();
                }
                else{
                    $template = null;
                }

                $testResult->projectCandidate->candidate->notify(new TestResultNotification($testResult, $userProfile, $template));

                if ($user)
                {
                    if ($user->is_send_email_test_completed_notification)
                    {
                        $user->notify(new TestCompletedNotification(
                            $testResult,
                            $user->name,
                            $user->middle_name
                        ));
                    }
                }

                $this->eventService->createEvent(
                    Event::EVENT_TYPE_SEND_TEST_END_NOTIFICATION,
                    Event::EVENT_SUB_TYPE_RESULT,
                    $testResult->projectCandidate->candidate->customer_id,
                    $testResult->test_id,
                    $testResult->project_candidate_id
                );

                return $this->json(
                    $this->testResultService->getPublicByToken($token)
                );
            }

            return $this->json();
        }
    }

    public function updateTestState(TestResult $testResult, bool $isFinished = false)
    {
        if ($isFinished)
        {
            $testResult->finished_at = Carbon::now();
        }
        $testResult->is_passed = $testResult->questionTotalValue() >= $testResult->test->pass_point_value;
        $testResult->save();

        /** @var Event $event */
        $event = $this->eventService->createEvent(
            Event::EVENT_TYPE_TESTING,
            Event::EVENT_SUB_TYPE_END_TEST,
            $testResult->projectCandidate->candidate->customer_id,
            $testResult->test_id,
            $testResult->project_candidate_id,
            $testResult->id
        );

        $event->is_popup_notification = true;
        $event->save();
    }
}
