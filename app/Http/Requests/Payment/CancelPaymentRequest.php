<?php


namespace App\Http\Requests\Payment;


use App\Db\Entity\Payment;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

/**
 * CancelPaymentRequest
 *
 * @property int $paymentId
 *
 */
class CancelPaymentRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'paymentId' => [
                'required',
                Rule::exists(Payment::class, 'id')
            ],
        ];
    }
}
