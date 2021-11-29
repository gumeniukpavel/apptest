<?php

namespace App\Http\Controllers\Api\StaffSpecialization;

use App\Db\Service\StaffSpecializationDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\StaffSpecialization\GetListRequest;
use App\Service\AuthService;

class StaffSpecializationController extends BaseController
{
    protected StaffSpecializationDao $staffSpecializationDao;

    public function __construct(
        StaffSpecializationDao $staffSpecializationDao,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->staffSpecializationDao = $staffSpecializationDao;
    }

    public function actionGetList(GetListRequest $request)
    {
        $user = $this->authService->getUser();
        $staffLevels = $this->staffSpecializationDao->search($request, $user);

        return $this->json(
            $staffLevels
        );
    }
}
