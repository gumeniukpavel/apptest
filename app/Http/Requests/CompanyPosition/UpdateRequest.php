<?php

namespace App\Http\Requests\CompanyPosition;

use App\Db\Entity\CompanyPosition;
use Illuminate\Validation\Rule;

/**
 * UpdateRequest
 *
 * @property int $id
 * @property string $name
 * @property int $vacancyCount
 */
class UpdateRequest extends AddRequest
{
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                'id' => [
                    'required',
                    Rule::exists(CompanyPosition::class, 'id')
                ],
            ]
        );
    }
}
