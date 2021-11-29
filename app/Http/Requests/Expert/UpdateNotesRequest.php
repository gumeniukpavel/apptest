<?php

namespace App\Http\Requests\Expert;

use App\Db\Entity\Expert;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateNotesRequest
 *
 * @property int $id
 * @property string $notes
 */
class UpdateNotesRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id'=> [
                Rule::exists(Expert::class, 'id'),
                'required'
            ],
            'notes' => 'string|nullable|max:5000'
        ];
    }
}
