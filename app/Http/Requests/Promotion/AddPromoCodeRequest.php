<?php


namespace App\Http\Requests\Promotion;


use Illuminate\Foundation\Http\FormRequest;
/**
 * AddPromoCodeRequest
 *
 * @property string $promo_code
 */
class AddPromoCodeRequest extends  FormRequest
{
    public function rules()
    {
        return [
            'promo_code' => 'required'
        ];
    }
}
