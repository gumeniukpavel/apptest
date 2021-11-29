<?php


namespace App\Http\Requests\Tariff;


use App\Db\Entity\TariffUser;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateTariffUserRequest
 *
 * @property int $tariffUserId
 */
class UpdateTariffUserRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'tariffUserId' => [
                'integer',
                'required',
                    Rule::exists(TariffUser::class, 'id')
            ]
        ];
    }
}
