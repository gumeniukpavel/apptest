<?php

namespace App\Http\Requests;

use App\Constant\Localization;
use App\Rules\IsEnumValueRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * GetLocalizationRequest
 *
 * @property string $locale
 */
class GetLocalizationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'locale' => [
                'required',
                new IsEnumValueRule(Localization::class)
            ]
        ];
    }
}
