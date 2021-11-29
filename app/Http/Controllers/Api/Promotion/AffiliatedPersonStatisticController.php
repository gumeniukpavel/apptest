<?php


namespace App\Http\Controllers\Api\Promotion;

use App\Db\Service\AffiliatedPersonStatisticDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Promotion\AddPromoCodeRequest;
use App\Http\Requests\Promotion\GetStatisticsRequest;
use App\Service\AuthService;
use Illuminate\Http\JsonResponse;

class AffiliatedPersonStatisticController extends BaseController
{
    protected AffiliatedPersonStatisticDao $affiliatedPersonStatisticDao;

    public function __construct(
        AuthService $authService,
        AffiliatedPersonStatisticDao $affiliatedPersonStatisticDao
    )
    {
        parent::__construct($authService);
        $this->affiliatedPersonStatisticDao = $affiliatedPersonStatisticDao;
    }

    public function getStatistics(GetStatisticsRequest $request): JsonResponse
    {
        $statistics = $this->affiliatedPersonStatisticDao->getAffiliatedPersonStatistics($request);
        return $this->json($statistics);
    }

    public function addPromo(AddPromoCodeRequest $request): void
    {
        $this->affiliatedPersonStatisticDao->addPromoCode($request->promo_code);
    }
}
