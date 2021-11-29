<?php

namespace App\Http\Requests\Question;

use App\Db\Entity\Test;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * GetListRequest
 *
 * @property int $testId
 * @property int $page
 */
class GetListRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'testId' => [
                'required',
                Rule::exists(Test::class, 'id')
            ],
            'page' => 'integer',
        ];
    }
}
