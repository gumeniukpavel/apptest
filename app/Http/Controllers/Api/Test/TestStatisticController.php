<?php

namespace App\Http\Controllers\Api\Test;

use App\Db\Entity\Test;
use App\Db\Service\TestDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IdRequest;
use App\Http\Requests\Test\AddTestRequest;
use App\Http\Requests\Test\GetListRequest;
use App\Http\Requests\Test\GetListStatisticRequest;
use App\Http\Requests\Test\UpdateTestRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;
use Illuminate\Http\Request;

// фасады для работы с файлами
use Illuminate\Support\Facades\Response;

use Carbon\Carbon;

class TestStatisticController extends BaseController
{
    protected TestDao $testService;

    public function __construct(TestDao $testDao, AuthService $authService)
    {
        parent::__construct($authService);
        $this->testService = $testDao;
    }

    public function actionGetStatistic(GetListStatisticRequest $request)
    {
        $statisticQuery = $this->testService->getTestsStatisticQuery(
            $this->user(),
            $request
        );

        return $this->json(
            new PaginationResource($statisticQuery, $request->getPage())
        );
    }
}
