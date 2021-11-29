<?php

namespace App\Http\Requests\Candidate;

use App\Constant\CandidateType;
use App\Db\Entity\Candidate;
use App\Rules\IsEnumValueRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ChangeTypeRequest
 *
 * @property int $id
 * @property string $type
 */
class ChangeTypeRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists(Candidate::class, 'id')
            ],
            'type' => [
                'required',
                new IsEnumValueRule(CandidateType::class)
            ]
        ];
    }
}
