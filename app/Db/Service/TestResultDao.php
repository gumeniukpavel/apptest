<?php

namespace App\Db\Service;

use App\Db\Entity\ProjectCandidate;
use App\Db\Entity\PublicEntity\PublicTestResult;
use App\Db\Entity\Question;
use App\Db\Entity\Test;
use App\Db\Entity\TestResult;
use App\Db\Entity\TestResultAnswer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TestResultDao
{
    /**
     * Предварительно создать список результатов тестов
     * при отправке письма-приглашения для кандидата, что бы
     * зафиксировать список тестов(и их порядок) и вопров к ним на момент отправки приглашения
     *
     * @param ProjectCandidate $projectCandidate
     * @param Test $testToBuild
     * @return TestResult|null
     */
    public function buildTestResultsPackForUser(ProjectCandidate $projectCandidate, Test $testToBuild) : ?TestResult
    {
        /** @var TestResult $result */
        $result = DB::transaction(function () use($projectCandidate, $testToBuild) {
            $testResult = new TestResult();
            $testResult->test_id = $testToBuild->id;
            $testResult->project_candidate_id = $projectCandidate->id;
            $testResult->access_token = Str::random(42);
            $testResult->access_token_created_at = Carbon::now();
            $testResult->save();

            $resultPosition = 0;
            $questions = $testToBuild->questions()->limit($testToBuild->max_allowed_questions)->inRandomOrder()->get();
            foreach ($questions as $question)
            {
                $testResultAnswer = new TestResultAnswer();
                $testResultAnswer->question_id = $question->id;
                $testResultAnswer->position = ++$resultPosition;
                $testResult->answers()->save($testResultAnswer);
            }

            return $testResult;
        });

        return $result;
    }

    public function getOneByToken(string $token) : ?TestResult
    {
        /** @var TestResult $testResult */
        $testResult = TestResult::query()
            ->with('candidate')
            ->with('test', function (BelongsTo $builder) {
                $builder->withCount('questions');
            })
            ->where('access_token', $token)
            ->first();

        return $testResult;
    }

    public function getOneById(string $id) : ?TestResult
    {
        /** @var TestResult $testResult */
        $testResult = TestResult::query()
            ->with('candidate')
            ->with('answers', function (HasMany $builder) {
                $builder->with('answer');
                $builder->with('question');
            })
            ->with('test', function (BelongsTo $builder) {
                $builder->withCount('questions');
            })
            ->where('id', $id)
            ->first();

        return $testResult;
    }

    public function getPublicByToken(?string $token) : ?PublicTestResult
    {
        /** @var PublicTestResult $testResult */
        $testResult = PublicTestResult::query()
            ->with('candidate')
            ->withCount('answeredAnswers')
            ->with('test', function (BelongsTo $builder) {
                $builder->withCount('testQuestions');
            })
            ->where('access_token', $token)
            ->first();
        return $testResult;
    }
}
