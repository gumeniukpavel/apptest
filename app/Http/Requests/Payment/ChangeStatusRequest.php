<?php

namespace App\Http\Requests\Payment;

use App\Db\Entity\Payment;
use App\Db\Entity\User;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

/**
 * GetListRequest
 *
 * @property int $paymentId
 * @property string $status
 * @property string $notes
 * @property string $transactionNumber
 */
class ChangeStatusRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'paymentId' => [
                'required',
                Rule::exists(Payment::class, 'id')
            ],
            'status' => [
                'nullable',
                Rule::in(Payment::PAYMENT_STATUSES)
            ],
            'notes' => 'nullable|string|max:512',
            'transactionNumber' => 'string|max:150'
        ];
    }
}
