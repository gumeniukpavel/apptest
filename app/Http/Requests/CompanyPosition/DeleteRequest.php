<?php

namespace App\Http\Requests\CompanyPosition;

use App\Db\Entity\CompanyPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class DeleteRequest
 * @package App\Http\Requests\Project
 *
 * @property int $id
 */
class DeleteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists(CompanyPosition::class, 'id')
            ],
        ];
    }
}
