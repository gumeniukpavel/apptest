<?php

namespace App\Http\Controllers\Api\User;

use App\Db\Service\UserEventDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\User\GetListUserEventRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;

class UserEventController extends BaseController
{
    private UserEventDao $userEventDao;

    public function __construct(UserEventDao $userEventDao, AuthService $authService)
    {
        parent::__construct($authService);
        $this->userEventDao = $userEventDao;
    }

    public function actionGetList(GetListUserEventRequest $request)
    {
        $searchQuery = $this->userEventDao->getSearchQuery(
            $this->user(),
            $request
        );

        return $this->json(
            new PaginationResource($searchQuery, $request->page, 100)
        );
    }

    public function actionGetOne(int $id)
    {
        $userEvent = $this->userEventDao->getOne($id);

        if ($this->user()->cannot('view', $userEvent))
        {
            return $this->responsePermissionsDenied();
        }
        return $this->json(
            $userEvent
        );
    }
}
