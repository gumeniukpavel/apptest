<?php

namespace App\Http\Controllers\Api\Promotion;

use App\Db\Service\AffiliatedPersonDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Promotion\AddAffiliatedPersonRequest;
use App\Http\Requests\Promotion\DeleteAffiliatedPersonRequest;
use App\Http\Requests\Promotion\UpdateAffiliatedPersonRequest;
use App\Service\AuthService;
use Illuminate\Http\JsonResponse;

class AffiliatedPersonController extends BaseController
{
    protected AffiliatedPersonDao $affiliatedPersonDao;
    protected  AuthService $authService;

    public function __construct(
        AffiliatedPersonDao $affiliatedPersonDao,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->affiliatedPersonDao = $affiliatedPersonDao;
    }

    public function affiliatedPersonList()
    {
         return $this->affiliatedPersonDao->list();
    }

    /**
     * @param AddAffiliatedPersonRequest $request
     * @return JsonResponse
     */
    public function actionAddAffiliatedPerson(AddAffiliatedPersonRequest $request): JsonResponse
    {
        $affiliatedPerson = $this->affiliatedPersonDao->add($request);

        if(!$affiliatedPerson)
        {
            return $this->jsonError();
        }

        return $this->json(
            $affiliatedPerson
        );
    }

    public function actionUpdateAffiliatedPerson(UpdateAffiliatedPersonRequest $request): JsonResponse
    {
        $affiliatedPerson = $this->affiliatedPersonDao->first($request->id);

        $updatedAffiliatedPerson = $this->affiliatedPersonDao->update($affiliatedPerson, $request);
        if (!$updatedAffiliatedPerson)
        {
            return $this->jsonError();
        }

        return $this->json(
            $affiliatedPerson
        );
    }

    /**
     * @param DeleteAffiliatedPersonRequest $request
     * @return JsonResponse
     */
    public function actionDeleteAffiliatedPerson(DeleteAffiliatedPersonRequest $request): JsonResponse
    {
        $affiliatedPerson = $this->affiliatedPersonDao->first($request->id);
        if (!$affiliatedPerson)
        {
            return $this->jsonError();
        }

        $this->affiliatedPersonDao->delete($affiliatedPerson);

        return $this->json([], 204);
    }
}
