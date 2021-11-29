<?php

namespace App\Http\Requests\Expert;

use App\Db\Entity\Expert;
use App\Db\Entity\UserEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * InterviewInvitationRequest
 *
 * @property int $expertId
 * @property int $userEventId
 */
class InterviewInvitationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'expertId'=> [
                Rule::exists(Expert::class, 'id'),
                'required'
            ],
            'userEventId' => [
                Rule::exists(UserEvent::class, 'id'),
                'required'
            ]
        ];
    }
}
