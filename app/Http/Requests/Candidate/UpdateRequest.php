<?php

namespace App\Http\Requests\Candidate;

use App\Db\Entity\CandidateFile;
use App\Db\Entity\CompanyPosition;
use App\Rules\IsYearLessThanDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateRequest
 *
 * @property int $id
 * @property string $name
 * @property string $middleName
 * @property string $surname
 * @property int $imageId
 * @property int $careerStartYear
 * @property int $specialtyWorkStartYear
 * @property int $companyPositionId
 * @property int $birthDate
 * @property string $email
 * @property string $phone
 * @property array $tags
 * @property string $staffLevel
 * @property string $staffSpecialization
 */
class UpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => 'required|integer',
            'name' => 'required|string|min:3|max:255',
            'middleName' => 'string|max:255|nullable',
            'surname' => 'required|string|min:3|max:255',
            'imageId' => [
                'nullable',
                Rule::exists(CandidateFile::class, 'id')
            ],
            'careerStartYear' => [
                'required',
                'integer',
                new IsYearLessThanDate(
                    $this->birthDate,
                    trans('candidates.careerYearGreaterThanBirthDayError')
                )
            ],
            'specialtyWorkStartYear' => [
                'nullable',
                'integer',
                new IsYearLessThanDate(
                    $this->birthDate,
                    trans('candidates.specialtyWorkYearGreaterThanBirthDayError')
                )
            ],
            'companyPositionId' => [
                'nullable',
                Rule::exists(CompanyPosition::class, 'id')
            ],
            'birthDate' => 'required',
            'email' => 'required|string|max:255|email:rfc',
            'phone' => 'required|integer|between:100000000000,999999999999',
            'tags' => 'array|nullable',
            'tags.*' => ['string', 'max:255'],
            'staffLevel' => 'nullable',
            'staffSpecialization' => 'nullable'
        ];
    }
}
