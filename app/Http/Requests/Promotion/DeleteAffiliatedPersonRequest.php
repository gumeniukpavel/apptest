<?php

namespace App\Http\Requests\Promotion;

use App\Db\Entity\Promotion\AffiliatedPerson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * DeleteAffiliatedPersonRequest
 *
 * @property int $id
 */
class DeleteAffiliatedPersonRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists(AffiliatedPerson::class, 'id')
            ]
        ];
    }
}
