<?php

namespace App\Http\Requests\Expert;

use App\Db\Entity\Expert;
use App\Db\Entity\ExpertFile;
use App\Rules\IsYearLessThanDate;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * AddRequest
 *
 * @property string $name
 * @property string $middleName
 * @property string $surname
 * @property int $imageId
 * @property int $careerStartYear
 * @property int $specialtyWorkStartYear
 * @property int $birthDate
 * @property string $email
 * @property string $phone
 * @property array $tags
 * @property string $staffLevel
 * @property string $staffSpecialization
 */
class AddRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'middleName' => 'string|max:255|nullable',
            'surname' => 'required|string|min:3|max:255',
            'imageId' => [
                'nullable',
                Rule::exists(ExpertFile::class, 'id')
            ],
            'careerStartYear' => [
                'required',
                'integer',
                new IsYearLessThanDate(
                    $this->birthDate,
                    trans('experts.careerYearGreaterThanBirthDayError')
                )
            ],
            'specialtyWorkStartYear' => [
                'nullable',
                'integer',
                new IsYearLessThanDate(
                    $this->birthDate,
                    trans('experts.specialtyWorkYearGreaterThanBirthDayError')
                )
            ],
            'birthDate' => 'required|integer',
            'email' => 'required|string|max:255|email:rfc',
            'phone' => 'required|integer|between:100000000000,999999999999',
            'tags' => 'array|nullable',
            'tags.*' => 'string|max:255',
            'staffLevel' => 'nullable',
            'staffSpecialization' => 'nullable'
        ];
    }

    public function updateEntity(Expert &$expert): Expert
    {
        $expert->name = $this->name;
        $expert->middle_name = $this->middleName;
        $expert->surname = $this->surname;
        $expert->email = $this->email;
        $expert->phone = $this->phone;
        $expert->career_start_year = $this->careerStartYear;
        $expert->specialty_work_start_year = $this->specialtyWorkStartYear;
        $expert->birth_date = Carbon::createFromTimestamp($this->birthDate)->format('Y-m-d');

        return $expert;
    }
}
