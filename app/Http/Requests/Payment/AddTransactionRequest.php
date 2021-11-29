<?php

namespace App\Http\Requests\Payment;

use App\Db\Entity\User;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

/**
 * AddTransactionRequest
 *
 * @property int $userId
 * @property integer $sum
 */
class AddTransactionRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'userId' => [
                'required',
                Rule::exists(User::class, 'id')
            ],
            'sum' => [
                'required',
                'numeric'
            ]
        ];
    }
}
