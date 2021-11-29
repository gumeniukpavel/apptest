<?php

namespace App\Http\Requests\Expert;

use App\Db\Entity\TestResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * GetApprovalRequestListRequest
 *
 * @property int $testResultId
 * @property int $page
 */
class GetApprovalRequestListRequest extends FormRequest
{
    public function rules()
    {
        return [
            'testResultId' => [
                'required',
                Rule::exists(TestResult::class, 'id')
            ],
            'page' => 'integer'
        ];
    }
}
