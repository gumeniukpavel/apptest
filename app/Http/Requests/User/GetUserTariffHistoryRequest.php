<?php


namespace App\Http\Requests\User;


use App\Constant\OrderType;
use App\Db\Entity\User;
use App\Http\Requests\ApiFormRequest;
use App\Rules\IsEnumValueRule;
use Illuminate\Validation\Rule;

/**
 * GetUserTariffHistoryRequest
 *
 * @property int $userId
 * @property int | null  $activeDateFrom
 * @property int | null  $activeDateTo
 * @property int | null $deActiveDateFrom
 * @property int | null $deActiveDateTo
 * @property int | null $paymentDateFrom
 * @property int | null $paymentDateTo
 * @property string | null $status
 * @property string | null $orderType
 */
class GetUserTariffHistoryRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'userId' => [
                'required',
                Rule::exists(User::class, 'id')
            ],
            'activeDateFrom' => 'nullable|integer',
            'activeDateTo' => 'nullable|integer',
            'deActiveDateFrom' => 'nullable|integer',
            'deActiveDateTo' => 'nullable|integer',
            'paymentDateFrom' => 'nullable|integer',
            'paymentDateTo' => 'nullable|integer',
            'status' => 'nullable|string',
            'orderType' => [
                'nullable',
                new IsEnumValueRule(OrderType::class)
            ],
        ];
    }
}
