<?php

namespace App\Http\Controllers\Api\Category;

use App\Db\Service\CategoryDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Category\GetListStatisticRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;

class CategoryStatisticController extends BaseController
{
    protected CategoryDao $categoryService;

    public function __construct(CategoryDao $categoryDao, AuthService $authService)
    {
        parent::__construct($authService);
        $this->categoryService = $categoryDao;
    }

    public function actionGetStatistic(GetListStatisticRequest $request)
    {
        $statisticQuery = $this->categoryService->getCategoriesStatisticQuery(
            $this->user()
        );
        return $this->json(
            new PaginationResource($statisticQuery, $request->getPage())
        );
    }
}
