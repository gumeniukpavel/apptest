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
use App\Db\Service\TestResultDao;
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

class TestResultController extends BaseController
{
    protected TestResultDao $testResultDao;

    public function __construct(
        TestResultDao $testResultDao,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->testResultDao = $testResultDao;
    }

    public function actionShowDetail(int $id)
    {
        $testResult = $this->testResultDao->getOneById($id);
        if ($this->user()->cannot('view', $testResult->test)) {
            return $this->responsePermissionsDenied();
        }

        return $testResult;
    }
}
