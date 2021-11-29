<?php

namespace App\Http\Requests\EmployeeRegistries;

use App\Db\Entity\EmployeeRegistries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * UploadDocumentRequest
 *
 * @property int $employeeRegistriesId
 * @property UploadedFile $document
 */
class UploadDocumentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'employeeRegistriesId' => [
                'required',
                Rule::exists(EmployeeRegistries::class, 'id')
            ],
            'document' => [
                'required',
                'max: 20000',
                'mimes:doc,docx,pdf'
            ]
        ];
    }
}
