<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{

    public function rules()
    {
        return [
            'candidatesFile' => 'required |mimes:csv '
        ];
    }
}
