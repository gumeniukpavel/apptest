<?php

namespace App\Http\Requests\Expert;

use App\Db\Entity\Expert;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ExportExpertDataRequest
 *
 * @property int $expertId
 */

class ExportExpertDataRequest extends FormRequest
{

    public function rules()
    {
        return [
            'expertId' => [
                'required',
                Rule::exists(Expert::class, 'id')
            ]
        ];
    }
}
