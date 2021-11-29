<?php

namespace App\Db\Service;

use App\Constant\TestType;
use App\Db\Entity\Event;
use App\Db\Entity\Question;
use App\Db\Entity\QuestionAnswer;
use App\Db\Entity\Test;
use App\Db\Entity\User;
use App\Http\Requests\Question\AddQuestionAnswerRequest;
use App\Http\Requests\Question\AddRequest;
use App\Http\Requests\Question\UpdateRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class QuestionDao
{
    private QuestionAnswerDao $questionAnswerDao;

    protected EventDao $eventService;

    public function __construct(
        QuestionAnswerDao $questionAnswerDao,
        EventDao $eventService
    )
    {
        $this->questionAnswerDao = $questionAnswerDao;
        $this->eventService = $eventService;
    }

    public function getOne(int $id) : ?Question
    {
        /** @var Question $record */
        $record = Question::query()->where('id', $id)->first();
        return $record;
    }

    public function getOneWithData(int $id) : ?Question
    {
        /** @var Question $record */
        $record = Question::query()
            ->with(['audios', 'images', 'videos'])
            ->with('answers')
            ->with('category')
            ->where('id', $id)
            ->first();
        return $record;
    }

    public function getPaginationQuery(?int $testId) : Builder
    {
        return Question::with('category')
            ->where('test_id', $testId)
            ->orderByDesc('updated_at');
    }

    public function addNewFromRequest(AddRequest $request, Test $test, User $user): Question
    {
        $question = new Question();
        DB::transaction(function () use($request, $test, $user, $question) {
            $question->description = $request->description;
            $question->category_id = $test->category_id;
            $question->level_id = $test->level_id;
            $question->user_id = $user->id;
            $question->test_id = $request->testId;
            $question->score = $request->score;
            $question->is_free_entry = $request->isFreeEntry ? $request->isFreeEntry : false;
            $question->save();
            $this->eventService->createEvent(Event::EVENT_TYPE_QUESTION, Event::EVENT_SUB_TYPE_CREATE, $user->id, $question->id, $test->id);

            if (!empty($request->answers))
            {
                $this->addAnswersToQuestion($question, $request->answers);
            }
        });
        return $question;
    }

    public function addAnswersToQuestion(Question $question, $answers)
    {
        foreach ($answers as $answerRequest)
        {
            $answer = new QuestionAnswer();
            if ($answerRequest instanceof AddQuestionAnswerRequest)
            {
                $answer->answer = $answerRequest->answer;
                $answer->is_right = $answerRequest->isRight;
            }
            else
            {
                $answer->answer = $answerRequest['answer'];
                $answer->is_right = $answerRequest['isRight'];
            }
            $question->answers()->save($answer);
        }
    }

    public function updateFromRequest(UpdateRequest $request, Question $question)
    {
        DB::transaction(function () use($request, $question) {
            $question->description = $request->description;
            $question->score = $request->score;
            $question->is_free_entry = $request->isFreeEntry ? $request->isFreeEntry : false;
            $question->save();

            if (!empty($request->answers))
            {
                $answerIdsToSync = [];
                foreach ($request->answers as $answerRequest)
                {
                    /** @var QuestionAnswer $answer */
                    $answer = null;
                    if ($answerRequest->id)
                    {
                        $answer = $question->answers()->where('id', $answerRequest->id)->first();
                    }
                    if (!$answer)
                    {
                        $answer = new QuestionAnswer();
                    }
                    $answer->answer = $answerRequest->answer;
                    $answer->is_right = $answerRequest->isRight;
                    $question->answers()->save($answer);
                    $answerIdsToSync[] = $answer->id;
                }
                // Delete others
                $question->answers()->whereNotIn('id', $answerIdsToSync)->delete();
            }
        });
    }

    public function checkQuestionExists(
        User $user,
        string $description = null
    ): ?Question
    {
        return Question::query()
            ->where('user_id', $user->id)
            ->when(!empty($description), function (Builder $builder) use ($description)
            {
                $builder->where('description', $description);
            })
            ->first();
    }

    public function deleteQuestion(Question $question)
    {
        $testResultAnswers = $question->testResultAnswers;
        foreach ($testResultAnswers as $testResultAnswer)
        {
            $this->eventService->markEventAsDeleted($testResultAnswer->id, Event::EVENT_TYPE_TEST_RESULT_ANSWER);
            $testResultAnswer->delete();
        }
        $question->answers()->delete();
        $this->eventService->markEventAsDeleted($question->id, Event::EVENT_TYPE_QUESTION);
        $question->delete();
    }
}
