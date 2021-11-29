<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->first() ?? 'Некорректные данные';
        throw new HttpResponseException(response()->json(['message' => $error], 400));
    }
}
