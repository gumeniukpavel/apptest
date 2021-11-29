<?php

namespace App\Http\Controllers\Api\Tag;

use App\Db\Service\TagsDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Tag\GetListRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;

class TagController extends BaseController
{
    protected TagsDao $tagsDao;

    public function __construct(
        TagsDao $tagsDao,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->tagsDao = $tagsDao;
    }

    public function actionSearch(GetListRequest $request)
    {
        $user = $this->authService->getUser();
        $tags = $this->tagsDao->search($request, $user);

        return $this->json(
            $tags
        );
    }
}
