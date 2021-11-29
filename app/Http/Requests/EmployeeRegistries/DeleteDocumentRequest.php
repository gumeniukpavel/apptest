<?php

namespace App\Http\Requests\EmployeeRegistries;

use App\Db\Entity\EmployeeRegistries;
use App\Db\Entity\EmployeeRegistriesFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * DeleteDocumentRequest
 *
 * @property int $employeeRegistriesId
 * @property int $documentId
 */
class DeleteDocumentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'employeeRegistriesId' => [
                'required',
                Rule::exists(EmployeeRegistries::class, 'id')
            ],
            'documentId' => [
                'required',
                Rule::exists(EmployeeRegistriesFile::class, 'id')
            ],
        ];
    }
}
