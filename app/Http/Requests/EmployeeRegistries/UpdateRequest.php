<?php

namespace App\Http\Requests\EmployeeRegistries;

use App\Db\Entity\Candidate;
use App\Db\Entity\EmployeeRegistries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateRequest
 *
 * @property int $id
 * @property int $employeeId
 * @property string $event
 * @property string $eventDetails
 * @property int $date
 * @property int $orderNumber
 * @property string $notes
 */
class UpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists(EmployeeRegistries::class, 'id')
            ],
            'employeeId' => [
                'required',
                Rule::exists(Candidate::class, 'id')
            ],
            'event' => 'required|string|max:255',
            'eventDetails' => 'nullable|string|max:255',
            'date' => 'required|integer',
            'orderNumber' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255'
        ];
    }
}
