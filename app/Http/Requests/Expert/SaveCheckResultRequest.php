<?php

namespace App\Http\Requests\Expert;

use App\Constant\ResultOfChecking;
use App\Rules\IsEnumValueRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * SaveCheckResultRequest
 *
 * @property int $id
 * @property string $resultOfChecking
 * @property string $comment
 */
class SaveCheckResultRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => 'required|integer',
            'resultOfChecking' => [
                'required',new IsEnumValueRule(ResultOfChecking::class)
            ],
            'comment' => 'string|nullable|max:5000'
        ];
    }
}
