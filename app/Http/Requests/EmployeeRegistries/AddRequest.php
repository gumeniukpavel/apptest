<?php

namespace App\Http\Requests\EmployeeRegistries;

use App\Db\Entity\Candidate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * AddRequest
 *
 * @property int $employeeId
 * @property string $event
 * @property string $eventDetails
 * @property int $date
 * @property int $orderNumber
 * @property string $notes
 */
class AddRequest extends FormRequest
{
    public function rules()
    {
        return [
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
