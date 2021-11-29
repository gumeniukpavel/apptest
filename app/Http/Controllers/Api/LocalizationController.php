<?php

namespace App\Http\Controllers\Api;

use App\Db\Service\LocalizationDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\GetLocalizationRequest;
use App\Service\AuthService;
use Illuminate\Http\JsonResponse;

class LocalizationController extends BaseController
{
    protected LocalizationDao $localizationDao;

    public function __construct(
        LocalizationDao $localizationDao,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->localizationDao = $localizationDao;
    }

    public function actionGetLocalization(GetLocalizationRequest $request): JsonResponse
    {
        $localizations = $this->localizationDao->getFile($request->locale);

        if (!$localizations)
        {
            return $this->jsonError();
        }

        return $this->json(
            $localizations
        );
    }
}
