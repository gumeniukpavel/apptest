<?php

namespace App\Http\Requests\Expert;

use App\Constant\ApprovalRequestStatus;
use App\Rules\IsEnumValueRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * ApprovalResultRequest
 *
 * @property string $token
 * @property string $status
 */
class ApprovalResultRequest extends FormRequest
{
    public function rules()
    {
        return [
            'token' => 'required|string',
            'status' => [
                'required',
                new IsEnumValueRule(ApprovalRequestStatus::class)
            ]
        ];
    }
}
