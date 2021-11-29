<?php

namespace App\Http\Requests\Expert;

use App\Db\Entity\Expert;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * UpdateRequest
 *
 * @property int $id
 * @property string $name
 * @property string $middleName
 * @property string $surname
 * @property int $specializationId
 * @property int $imageId
 * @property int $careerStartYear
 * @property int $birthDate
 * @property string $email
 * @property string $phone
 * @property array $tags
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
                    Rule::exists(Expert::class, 'id')
                ],
            ]
        );
    }
}
