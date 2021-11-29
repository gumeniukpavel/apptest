<?php

namespace App\Http\Requests\EmployeeRegistries;

use App\Db\Entity\EmployeeRegistries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class DeleteRequest
 * @package App\Http\Requests\EmployeeRegistries
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
                Rule::exists(EmployeeRegistries::class, 'id')
            ],
        ];
    }
}
