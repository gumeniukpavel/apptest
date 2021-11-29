<?php

namespace App\Http\Requests\Payment;

use App\Db\Entity\Tariff;
use App\Db\Entity\TariffPeriod;
use App\Db\Entity\TariffPrice;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

/**
 * GetListRequest
 *
 * @property int $tariffId
 * @property int $tariffPriceId
 */
class CreateRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'tariffId' => [
                'required',
                Rule::exists(Tariff::class, 'id')
            ],
            'tariffPriceId' => [
                'required',
                Rule::exists(TariffPrice::class, 'id')
            ],
        ];
    }
}
