<?php

namespace App\Http\Controllers\Api\StaffLevel;

use App\Db\Service\StaffLevelDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\StaffLevel\GetListRequest;
use App\Service\AuthService;

class StaffLevelController extends BaseController
{
    protected StaffLevelDao $staffLevelDao;

    public function __construct(
        StaffLevelDao $staffLevelDao,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->staffLevelDao = $staffLevelDao;
    }

    public function actionGetList(GetListRequest $request)
    {
        $user = $this->authService->getUser();
        $staffLevels = $this->staffLevelDao->search($request, $user);

        return $this->json(
            $staffLevels
        );
    }
}
