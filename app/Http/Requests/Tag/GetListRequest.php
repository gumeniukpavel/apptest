<?php

namespace App\Http\Requests\Tag;

use App\Http\Requests\ApiFormRequest;

/**
 * GetListRequest
 *
 * @property int $page
 * @property string $searchString
 */
class GetListRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'searchString' => 'string|max:255|nullable',
        ];
    }
}
