<?php

namespace App\Http\Requests\Candidate;

use App\Db\Entity\Candidate;
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
                Rule::exists(Candidate::class, 'id'),
                'required'
            ],
            'notes' => 'string|nullable|max:5000'
        ];
    }
}
