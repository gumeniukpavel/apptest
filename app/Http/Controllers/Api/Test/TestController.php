<?php

namespace App\Http\Controllers\Api\Test;

use App\Constant\TestType;
use App\Db\Entity\Event;
use App\Db\Entity\TariffUser;
use App\Db\Entity\Test;
use App\Db\Service\EventDao;
use App\Db\Service\QuestionDao;
use App\Db\Service\TariffUserDao;
use App\Db\Service\TestDao;
use App\Db\Service\UserTariffTestDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IdRequest;
use App\Http\Requests\Question\AddRequest;
use App\Http\Requests\Question\ImportRequest;
use App\Http\Requests\Test\AddQuestionnaireRequest;
use App\Http\Requests\Test\AddTestRequest;
use App\Http\Requests\Test\GetListRequest;
use App\Http\Requests\Test\GetQuestionnaireListRequest;
use App\Http\Requests\Test\UpdateTestRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;

// фасады для работы с файлами
use Illuminate\Support\Facades\Response;

use Carbon\Carbon;

class TestController extends BaseController
{
    protected TestDao $testService;
    protected TariffUserDao $tariffUserDao;
    protected UserTariffTestDao $userTariffTestDao;
    protected EventDao $eventService;
    protected QuestionDao $questionService;

    public function __construct(
        TestDao $testDao,
        TariffUserDao $tariffUserDao,
        UserTariffTestDao $userTariffTestDao,
        EventDao $eventService,
        QuestionDao $questionService,
        AuthService $authService)
    {
        parent::__construct($authService);
        $this->testService = $testDao;
        $this->tariffUserDao = $tariffUserDao;
        $this->userTariffTestDao = $userTariffTestDao;
        $this->eventService = $eventService;
        $this->questionService = $questionService;
    }

    public function actionList(GetListRequest $request)
    {
        if (is_null($request->isTestsFromTariff))
        {
            $searchQuery = $this->testService->getAllSearchQuery(
                $this->user(),
                $request->categoryId,
                $request->levelId,
                $request->projectId,
                $request->searchString,
                $request->orderType
            );
        }
        elseif ($request->isTestsFromTariff)
        {
            $searchQuery = $this->testService->getTestsForTariff(
                $request->categoryId,
                $request->levelId,
                $request->projectId,
                $request->searchString,
                $request->orderType
            );
        }
        else
        {
            $searchQuery = $this->testService->getSearchQuery(
                $this->user(),
                $request->categoryId,
                $request->levelId,
                $request->projectId,
                $request->searchString,
                $request->orderType
            );
        }

        return $this->json(
            new PaginationResource($searchQuery, $request->page)
        );
    }

    public function actionShow(int $id)
    {
        $test = $this->testService->firstWithData($id);
        if ($this->user()->cannot('view', $test)) {
            return $this->responsePermissionsDenied();
        }
        return $test;
    }

    public function actionStore(AddTestRequest $request)
    {
        if ($this->user()->cannot('create', Test::class)) {
            return $this->responsePermissionsDenied();
        }

        $tariffUser = $this->checkingTariffRestrictions();
        if (!$tariffUser instanceof TariffUser)
        {
            return $tariffUser;
        }

        $test = $request->getEntity();
        $test->user_id = $this->user()->id;
        $test->save();
        if ($tariffUser)
        {
            $this->userTariffTestDao->createUserTariffTest($this->user(), $test, $tariffUser->tariff);
        }
        $this->eventService->createEvent(
            Event::EVENT_TYPE_TEST,
            Event::EVENT_SUB_TYPE_CREATE,
            $this->user()->id,
            $test->id
        );
        return $this->json(
            $this->testService->firstWithData($test->id)
        );
    }

    public function actionCreateQuestionnaire(AddQuestionnaireRequest $request)
    {
        if ($this->user()->cannot('create', Test::class)) {
            return $this->responsePermissionsDenied();
        }
        $questionnaire = $request->getEntity();
        $questionnaire->user_id = $this->user()->id;
        $questionnaire->save();
        $this->testService->createQuestionnaireTags($questionnaire, $this->user(), $request->tags);
        $this->eventService->createEvent(
            Event::EVENT_TYPE_QUESTIONNAIRE,
            Event::EVENT_SUB_TYPE_CREATE,
            $this->user()->id,
            $questionnaire->id
        );
        return $this->json(
            $this->testService->firstWithData($questionnaire->id)
        );
    }

    public function actionGetQuestionnaire(int $id)
    {
        $questionnaire = $this->testService->firstWithData($id);
        if ($this->user()->cannot('view', $questionnaire)) {
            return $this->responsePermissionsDenied();
        }
        return $questionnaire;
    }

    public function actionGetQuestionnaireList(GetQuestionnaireListRequest $request)
    {
        $searchQuery = $this->testService->getQuestionnaireList(
            $this->user(),
            $request->searchString,
            $request->projectId,
            $request->orderType,
            $request->tags
        );
        return $this->json(
            new PaginationResource($searchQuery, $request->page)
        );
    }

    public function update(UpdateTestRequest $request)
    {
        /** @var Test $test */
        $test = Test::query()->where('id', $request->id)->first();
        if ($this->user()->cannot('update', $test)) {
            return $this->responsePermissionsDenied();
        }
        if (!$test) {
            return $this->responseNotFound();
        }
        $test = $request->updateEntity($test);
        $test->save();
        if ($test->isTest())
        {
            $this->eventService->createEvent(
                Event::EVENT_TYPE_TEST,
                Event::EVENT_SUB_TYPE_UPDATE,
                $this->user()->id,
                $test->id
            );
        }
        else
        {
            $this->testService->createQuestionnaireTags($test, $this->user(), $request->tags);
            $this->eventService->createEvent(
                Event::EVENT_TYPE_QUESTIONNAIRE,
                Event::EVENT_SUB_TYPE_UPDATE,
                $this->user()->id,
                $test->id
            );
        }

        return $this->json(
            $this->testService->firstWithData($test->id),
            200
        );
    }

    public function delete(IdRequest $request)
    {
        $test = $this->testService->getOne($request->id);
        if ($this->user()->cannot('delete', $test)) {
            return $this->responsePermissionsDenied();
        }
        if ($test->isTest())
        {
            $this->eventService->createEvent(
                Event::EVENT_TYPE_TEST,
                Event::EVENT_SUB_TYPE_DELETE,
                $this->user()->id,
                $test->id
            );
        }
        else
        {
            $this->eventService->createEvent(
                Event::EVENT_TYPE_QUESTIONNAIRE,
                Event::EVENT_SUB_TYPE_DELETE,
                $this->user()->id,
                $test->id
            );
        }
        $this->testService->deleteTest($test);
        return $this->json(null, 204);
    }

    public function import(Request $request)
    {
        if (isset($request->tests_file)) {
            $tests_file = $request->file('tests_file');
            $extension = $tests_file->getClientOriginalExtension();
            if ($extension != 'csv') {
                return response()->json(['message' => "Ошибка. Тип файла для импорта должен быть CSV, разделитель - запятая."], 400);
            }
            // парсим файл построчно
            $loadss = file($tests_file);

            $count = count($loadss);

            $tariffUser = $this->checkingTariffRestrictions($count);
            if (!$tariffUser instanceof TariffUser)
            {
                return $tariffUser;
            }

            $success = [];
            for ($i = 1; $i < $count; $i++) {

                $str = $loadss[$i];

                if ($str[0] == ',') continue; // проверка на пустую строку

                $data = explode(",", $str);

                $s = [
                    'level_id' => $data[0],
                    'name' => $data[1],
                    'description' => $data[2]
                ];

                $req = Request::create('/api/test', 'POST', $s);

                if (TestController::store($req)->status() <= 201) {
                    array_push($success, $s);
                };

            }
            if (count($success)) {
                return $this->json([
                    'message' => "В базу данных системы успешно добавлены следующие кандидаты: ",
                    'tests' => $success
                ], 201);
            } else {
                return $this->responsePermissionsDenied();
            }

            //Storage::disk('public')->put($candidates_file->getFilename().'.'.$extension,  File::get($candidates_file));
            //$filepath = $candidates_file->getFilename().'.'.$extension;
            //dd($filepath);
        }
    }

    // export csv файла с данными тестов
    public function export()
    {
        $now = Carbon::Now('Europe/Kiev')->format("Y_m_d-H_i_s.u_");

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=" . $now . "tests_export.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        //$reviews = Reviews::getReviewExport($this->hw->healthwatchID)->get();
        $tests = Test::with('level')->with('users')->get();

        $columns = array(
            'id',
            'level_id',
            'test_level',
            'test_name',
            'test_description',
            'test_pass_point_value',
            'test_candidates',
            'created_at',
            'updated_at',
        );

        $callback = function () use ($tests, $columns)
        {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($tests as $test) {
                $test_candidates = "";
                foreach ($test->users as $user) {
                    $test_candidates .= "id " . $user->id . " - " . $user->name . " ";
                }
                fputcsv($file, array(
                    $test->id,
                    $test->level_id,
                    $test->level->level,
                    $test->name,
                    $test->description,
                    $test->pass_point_value,
                    $test_candidates,
                    $test->created_at,
                    $test->updated_at

                ));
            }
            fclose($file);
        };
        return Response::stream($callback, 200, $headers);
    }

    public function readCSV($csvFile, $array)
    {
        $file_handle = fopen($csvFile, 'r');
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 0, $array['delimiter']);
        }
        fclose($file_handle);
        return $line_of_text;
    }

    public function actionImport(ImportRequest $request)
    {
        $user = $this->authService->getUser();
        $load = $this->readCSV($request->questionFile, array('delimiter' => ','));
        $count = count($load);
        $succeed = 0;
        $errors = [];
        for ($i = 1; $i < $count-1; $i++)
        {
            $data = $load[$i];
            $answer = [
                'answer' => $data[2],
                'isRight' => boolval(trim($data[3]))
            ];

            $fields = [
                'description' => $data[0],
                'score' => $data[1],
                'testId' => $request->testId,
                'answers' => [
                    $answer
                ]
            ];

            $test = $this->testService->getOne($request->testId);
            $request = new AddRequest($fields);

            try
            {
                $validate = $request->validate($request->rules());

                if ($validate)
                {
                    $question = $this->questionService->checkQuestionExists($this->user(), $validate['description']);

                    if ($question)
                    {
                        $this->questionService->addAnswersToQuestion($question, $request->answers);
                    }
                    else
                    {
                        $this->questionService->addNewFromRequest($request, $test, $user);
                        $succeed++;
                    }
                }
            }
            catch (ValidationException $e)
            {
                if (count($errors) < 50)
                {
                    $errors[] = array(
                        'line' => $i,
                        'error' => $e->errors()
                    );
                }
            }
        }

        if (!empty($succeed))
        {
            return response()->json([
                'message' => "В базу данных системы успешно добавлены вопросы",
                'questions' => $succeed,
                'errors' => $errors
            ], 201);
        }
        else
        {
            return response()->json([
                'message' => "В базу данных системы не добавлено ни одного вопроса",
                'errors' => $errors
            ], 200);
        }
    }

    private function checkingTariffRestrictions($count = false)
    {
        $tariffUser = $this->tariffUserDao->getActiveByUser($this->user());
        if ($tariffUser)
        {
            if ($tariffUser->tariffIsOver)
            {
                $tariffUser->is_active = false;
                $tariffUser->save();
                return $this->jsonError();
            }

            if (!$tariffUser->tariff->is_unlimited_tests)
            {
                if ($count)
                {
                    $userTariffTestCount = $this->userTariffTestDao->getTestsCountByUser($this->user());
                    $difference = $tariffUser->tariff->candidates_count - $userTariffTestCount;
                    if ($difference < $count-1)
                    {
                        return $this->jsonError();
                    }
                }
                else
                {
                    $userTests = Test::query()->where([
                        'user_id' => $this->user()->id,
                        'type' => TestType::$Test->getValue()
                    ])->count();
                    if ($userTests >= $tariffUser->tariff->tests_count)
                    {
                        return $this->jsonError(trans('tariffs.noTariffForUserTest'));
                    }
                }
            }
        }
        else
        {
            return $this->jsonError(trans('tariffs.noTariffForUserTest'));
        }

        return $tariffUser;
    }
}
