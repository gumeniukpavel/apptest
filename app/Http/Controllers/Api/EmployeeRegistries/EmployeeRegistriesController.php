<?php

namespace App\Http\Controllers\Api\EmployeeRegistries;

use App\Constant\CandidateType;
use App\Db\Entity\Candidate;
use App\Db\Entity\EmployeeRegistries;
use App\Db\Entity\EmployeeRegistriesFile;
use App\Db\Service\EmployeeRegistriesDao;
use App\Db\Service\EmployeeRegistriesFileDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\EmployeeRegistries\AddRequest;
use App\Http\Requests\EmployeeRegistries\DeleteDocumentRequest;
use App\Http\Requests\EmployeeRegistries\DeleteRequest;
use App\Http\Requests\EmployeeRegistries\GetAllListRequest;
use App\Http\Requests\EmployeeRegistries\GetListRequest;
use App\Http\Requests\EmployeeRegistries\UpdateRequest;
use App\Http\Requests\EmployeeRegistries\UploadDocumentRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;
use App\Service\StorageService;

class EmployeeRegistriesController extends BaseController
{
    protected EmployeeRegistriesDao $employeeRegistries;
    protected EmployeeRegistriesFileDao $employeeRegistriesFileDao;
    protected StorageService $storageService;

    public function __construct(
        EmployeeRegistriesDao $employeeRegistries,
        EmployeeRegistriesFileDao $employeeRegistriesFileDao,
        StorageService $storageService,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->employeeRegistries = $employeeRegistries;
        $this->employeeRegistriesFileDao = $employeeRegistriesFileDao;
        $this->storageService = $storageService;
    }

    public function actionGetAllList(GetAllListRequest $request)
    {
        $employeeRegistriesQuery = $this->employeeRegistries->list(
            $this->user(),
            $request->searchString
        );
        return $this->json(
            new PaginationResource($employeeRegistriesQuery, $request->page)
        );
    }

    public function actionGetList(GetListRequest $request)
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $request->employeeId)->first();
        if ($this->user()->cannot('view', $candidate))
        {
            return $this->responsePermissionsDenied();
        }

        $employeeRegistriesQuery = $this->employeeRegistries->searchQuery(
            $this->user(),
            $candidate,
            $request->searchString,
            $request->orderType,
            $request->fromDate,
            $request->toDate
        );
        return $this->json(
            new PaginationResource($employeeRegistriesQuery, $request->page)
        );
    }

    public function actionGetOne(int $id)
    {
        $user = $this->authService->getUser();

        /** @var EmployeeRegistries $employeeRegistries */
        $employeeRegistries = EmployeeRegistries::byId($id);

        if ($user->cannot('view', $employeeRegistries)) {
            return $this->responsePermissionsDenied();
        }

        return $this->json(
            $employeeRegistries
        );
    }

    public function actionCreate(AddRequest $request)
    {
        $user = $this->authService->getUser();

        if ($user->cannot('create', EmployeeRegistries::class)) {
            return $this->responsePermissionsDenied();
        }

        /** @var Candidate $employee */
        $employee = Candidate::byId($request->employeeId);

        if ($employee->type == CandidateType::$Candidate->getValue())
        {
            return $this->jsonError(trans('candidates.candidateError'));
        }

        $this->employeeRegistries->create($request, $user);

        return $this->json();
    }

    public function actionUpdate(UpdateRequest $request)
    {
        $user = $this->authService->getUser();

        /** @var EmployeeRegistries $employeeRegistries */
        $employeeRegistries = EmployeeRegistries::query()->where([
            'id' => $request->id,
            'candidate_id' => $request->employeeId
        ])->first();

        if (!$employeeRegistries || $user->cannot('update', $employeeRegistries)) {
            return $this->responsePermissionsDenied();
        }

        $this->employeeRegistries->update($request, $employeeRegistries);

        return $this->json();
    }

    public function actionDelete(DeleteRequest $request)
    {
        /** @var EmployeeRegistries $employeeRegistries */
        $employeeRegistries = EmployeeRegistries::byId($request->id);

        if (!$employeeRegistries || $this->user()->cannot('delete', $employeeRegistries)) {
            return $this->responsePermissionsDenied();
        }

        $this->storageService->deleteEmployeeRegistries($employeeRegistries);

        return $this->json();
    }

    public function actionUploadDocument(UploadDocumentRequest $request)
    {
        /** @var EmployeeRegistries $employeeRegistries */
        $employeeRegistries = EmployeeRegistries::byId($request->employeeRegistriesId);
        if (!$employeeRegistries || $this->user()->cannot('update', $employeeRegistries))
        {
            return $this->responsePermissionsDenied();
        }

        if ($employeeRegistries->documents()->count() >= 10)
        {
            return $this->jsonError(trans('employeeRegistries.documentsError'));
        }

        $uploadedFile = $request->document;
        $responseFileEntity = $this->employeeRegistriesFileDao->createNew(
            $uploadedFile,
            $this->user()
        );
        $fileName = $this->storageService->saveEmployeeRegistriesDocumentFile($uploadedFile);
        $responseFileEntity->employee_registries_id = $employeeRegistries->id;
        $responseFileEntity->path = $fileName;
        $responseFileEntity->save();

        return $this->json();
    }

    public function actionDeleteDocument(DeleteDocumentRequest $request)
    {
        $user = $this->authService->getUser();

        /** @var EmployeeRegistries $employeeRegistries */
        $employeeRegistries = EmployeeRegistries::byId($request->employeeRegistriesId);
        /** @var EmployeeRegistriesFile $employeeRegistriesFile */
        $employeeRegistriesFile = EmployeeRegistriesFile::byId($request->documentId);

        if ($user->cannot('update', $employeeRegistries) || $user->cannot('delete', $employeeRegistriesFile))
        {
            return $this->responsePermissionsDenied();
        }

        $this->storageService->deleteEmployeeRegistriesDocumentFile($employeeRegistriesFile);

        return $this->json();
    }
}
