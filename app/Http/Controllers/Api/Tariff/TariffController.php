<?php

namespace App\Http\Controllers\Api\Tariff;

use App\Constant\TestType;
use App\Db\Entity\Event;
use App\Db\Entity\Test;
use App\Db\Entity\TariffUser;
use App\Db\Entity\UserTariffCategory;
use App\Db\Service\EventDao;
use App\Db\Service\TariffDao;
use App\Db\Service\TariffUserDao;
use App\Db\Service\UserTariffCandidateDao;
use App\Db\Service\UserTariffCategoryDao;
use App\Db\Service\UserTariffTestDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Tariff\UpdateTariffUserRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

use Carbon\Carbon;

class TariffController extends BaseController
{
    protected TariffDao $tariffService;
    protected UserTariffCandidateDao $userTariffCandidateDao;
    protected UserTariffCategoryDao $userTariffCategoryDao;
    protected UserTariffTestDao $userTariffTestDao;
    protected TariffUserDao $tariffUserDao;
    protected EventDao $eventService;

    public function __construct(
        TariffDao $tariffDao,
        UserTariffCandidateDao $userTariffCandidateDao,
        UserTariffCategoryDao $userTariffCategoryDao,
        UserTariffTestDao $userTariffTestDao,
        TariffUserDao $tariffUserDao,
        EventDao $eventService,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->tariffService = $tariffDao;
        $this->userTariffCandidateDao = $userTariffCandidateDao;
        $this->userTariffCategoryDao = $userTariffCategoryDao;
        $this->userTariffTestDao = $userTariffTestDao;
        $this->tariffUserDao = $tariffUserDao;
        $this->eventService = $eventService;
    }

    public function list()
    {
        $query = $this->tariffService->getListQuery();
        return $this->json(
            new PaginationResource($query, 1)
        );
    }

    public function actionPublicList()
    {
        $query = $this->tariffService->getPublicListQuery();
        return $this->json(
            new PaginationResource($query, 1)
        );
    }

    public function actionStatisticByUser()
    {
        $tariffUser = $this->tariffUserDao->getActiveByUser($this->user());
        $userTestsCount = Test::query()->where([
            'user_id' => $this->user()->id,
            'type' => TestType::$Test->getValue()
        ])->count();
        $userTariffTestsCount = $this->userTariffTestDao->getTestsCountByUser($this->user());
        $userTariffCandidateCount = $this->userTariffCandidateDao->getCandidatesCountByUser($this->user());
        $userTariffCategoriesCount = $this->userTariffCategoryDao->getCategoriesCountByUser($this->user());

        return $this->json([
            'tariff' => $tariffUser->tariff,
            'exists' => [
                'tests' => $userTestsCount,
                'tariffTests' => $userTariffTestsCount,
                'candidates' => $userTariffCandidateCount,
                'categories' => $userTariffCategoriesCount
            ]
        ]);
    }

    public function pauseTariffUser(UpdateTariffUserRequest $request): JsonResponse
    {
        /** @var TariffUser $tariffUser */
        $tariffUser = TariffUser::byId($request->tariffUserId);
        $tariffUser = $this->tariffUserDao->pauseTariff($tariffUser);

        return $this->json($tariffUser);
    }

    public function unPauseTariffUser(UpdateTariffUserRequest $request): JsonResponse
    {
        /** @var TariffUser $tariffUser */
        $tariffUser = TariffUser::byId($request->tariffUserId);

        $tariffUser = $this->tariffUserDao->unPauseTariff($tariffUser);

        return $this->json($tariffUser);
    }
}
