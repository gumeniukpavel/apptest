<?php

namespace App\Http\Controllers\Api\CompanyPosition;

use App\Db\Entity\CompanyPosition;
use App\Db\Service\CompanyPositionDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CompanyPosition\AddRequest;
use App\Http\Requests\CompanyPosition\DeleteRequest;
use App\Http\Requests\CompanyPosition\GetListRequest;
use App\Http\Requests\CompanyPosition\UpdateRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;

class CompanyPositionController extends BaseController
{
    protected CompanyPositionDao $companyPositionDao;

    public function __construct(
        CompanyPositionDao $companyPositionDao,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->companyPositionDao = $companyPositionDao;
    }

    public function actionGetList(GetListRequest $request)
    {
        $companyPositionQuery = $this->companyPositionDao->searchQuery(
            $this->user(),
            $request->searchString,
            $request->orderType
        );
        return $this->json(
            new PaginationResource($companyPositionQuery, $request->page)
        );
    }

    public function actionCreate(AddRequest $request)
    {
        $user = $this->authService->getUser();

        if ($user->cannot('create', CompanyPosition::class)) {
            return $this->responsePermissionsDenied();
        }

        $companyPosition = $this->companyPositionDao->create($user, $request);

        return $this->json(
            $companyPosition
        );
    }

    public function actionUpdate(UpdateRequest $request)
    {
        $user = $this->authService->getUser();

        /** @var CompanyPosition $companyPosition */
        $companyPosition = CompanyPosition::query()->where('id', $request->id)->first();

        if (!$companyPosition || $user->cannot('update', $companyPosition)) {
            return $this->responsePermissionsDenied();
        }

        $companyPosition = $this->companyPositionDao->update($request, $companyPosition);
        $companyPosition->save();

        return $this->json(
            $this->companyPositionDao->firstWithData($companyPosition->id)
        );
    }

    public function actionDelete(DeleteRequest $request)
    {
        /** @var CompanyPosition $companyPosition */
        $companyPosition = CompanyPosition::query()->where('id', $request->id)->first();

        if (!$companyPosition || $this->user()->cannot('delete', $companyPosition)) {
            return $this->responsePermissionsDenied();
        }

        $this->companyPositionDao->delete($companyPosition);
        return $this->json();
    }

    public function actionGetCandidatesList(int $id)
    {
        /** @var CompanyPosition $companyPosition */
        $companyPosition = CompanyPosition::byId($id);

        $candidates = $this->companyPositionDao->getEmployeeByCompanyPosition($companyPosition);

        return $this->json($candidates);
    }
}
