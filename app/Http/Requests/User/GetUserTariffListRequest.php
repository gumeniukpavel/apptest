<?php


namespace App\Http\Requests\User;


use App\Constant\OrderType;
use App\Constant\TariffUserStatus;
use App\Db\Entity\Tariff;
use App\Http\Requests\ApiFormRequest;
use App\Rules\IsEnumValueRule;
use Illuminate\Validation\Rule;

/**
 * GetUserTariffListRequest
 *
 * @property int $page
 * @property int | null $tariffId
 * @property string | null $status
 * @property int | null $activeDate
 * @property int | null $deActiveDate
 * @property string | null $orderType
 */
class GetUserTariffListRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer|nullable',
            'tariffId' => [
                'nullable',
                Rule::exists(Tariff::class, 'id')
            ],
            'status' => [
                'nullable',
                    Rule::in([
                        TariffUserStatus::$Pending->getValue(),
                        TariffUserStatus::$Active->getValue(),
                        TariffUserStatus::$Exhausted->getValue(),
                        TariffUserStatus::$Paused->getValue()
                    ])
            ],
            'activeDateFrom' => 'nullable|integer',
            'activeDateTo' => 'nullable|integer',
            'deActiveDateFrom' => 'nullable|integer',
            'deActiveDateTo' => 'nullable|integer',
            'orderType' => [
                'nullable',
                new IsEnumValueRule(OrderType::class)
            ],
        ];
    }
}
