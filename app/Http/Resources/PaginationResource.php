<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaginationResource extends JsonResource
{
    const PAGE_SIZE = 10;

    /**
     * @param EloquentBuilder | QueryBuilder $queryBuilder
     * @param $page
     */
    public function __construct($queryBuilder, $page, $pageSize = 10)
    {
        if (
            !$queryBuilder instanceof QueryBuilder
            && !$queryBuilder instanceof EloquentBuilder
        )
            throw new \Exception('Incorrect query builder type');

        $page = intval($page);
        $page = $page < 1 ? 1 : $page;
        $offset = $pageSize * ($page - 1);
        $totalItems = $queryBuilder->count();
        $listItems = $queryBuilder->limit($pageSize)->offset($offset)->get();
        parent::__construct([
            'totalItems' => $totalItems,
            'totalPages' => ceil($totalItems / $pageSize),
            'pageSize' => $pageSize,
            'items' => $listItems
        ]);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
