<?php

namespace App\Db\Service;

use App\Db\Entity\Expert;
use App\Db\Entity\ExpertInterviewEvent;
use App\Http\Requests\Expert\InterviewInvitationRequest;
use App\Service\AuthService;
use Illuminate\Support\Str;

class ExpertInterviewEventDao
{
    private AuthService $authService;

    public function __construct(
        AuthService $authService
    )
    {
        $this->authService = $authService;
    }

    public function createExpertInterviewEvent(InterviewInvitationRequest $request, Expert $expert): ExpertInterviewEvent
    {
        $expertInterviewEvent = new ExpertInterviewEvent();
        $user = $this->authService->getUser();
        $expertInterviewEvent->user_id = $user->id;
        $expertInterviewEvent->expert_id = $expert->id;
        $expertInterviewEvent->user_event_id = $request->userEventId;
        $expertInterviewEvent->status = ExpertInterviewEvent::EXPERT_EVENT_STATUS_PENDING;
        $expertInterviewEvent->access_token_yes = Str::random(42);
        $expertInterviewEvent->access_token_no = Str::random(42);

        $expertInterviewEvent->save();

        return $expertInterviewEvent;
    }

    public function getExpertEventByAgreedToken(string $token): ?ExpertInterviewEvent
    {
        /** @var ExpertInterviewEvent $expertInterviewEvent */
        $expertInterviewEvent = ExpertInterviewEvent::query()
            ->where('access_token_yes', $token)
            ->where('status', ExpertInterviewEvent::EXPERT_EVENT_STATUS_PENDING)
            ->first();

        return $expertInterviewEvent;
    }

    public function getExpertEventByCancelToken(string $token): ?ExpertInterviewEvent
    {
        /** @var ExpertInterviewEvent $expertInterviewEvent */
        $expertInterviewEvent = ExpertInterviewEvent::query()
            ->where('access_token_no', $token)
            ->where('status', ExpertInterviewEvent::EXPERT_EVENT_STATUS_PENDING)
            ->first();

        return $expertInterviewEvent;
    }
}
