<?php

namespace App\Db\Service;

use App\Db\Entity\Candidate;
use App\Db\Entity\User;
use App\Db\Entity\UserEvent;
use App\Http\Requests\Candidate\InterviewInvitationRequest;
use App\Http\Requests\User\GetListUserEventRequest;
use App\Service\AuthService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class UserEventDao
{
    private AuthService $authService;

    public function __construct(
        AuthService $authService
    )
    {
        $this->authService = $authService;
    }

    public function getOne(int $id): ?UserEvent
    {
        /** @var UserEvent $userEvent */
        $userEvent = UserEvent::query()
            ->where('id', $id)
            ->with(['user', 'candidate', 'expertInterviewEvent.expert'])
            ->first();
        return $userEvent;
    }

    public function getSearchQuery(User $user, GetListUserEventRequest $request): Builder
    {
        $builder = $this->getFilteredListQuery($request);
        return $builder->where('user_id', $user->id);
    }

    private function getFilteredListQuery(GetListUserEventRequest $request): Builder
    {
        return UserEvent::query()
            ->with(['user', 'candidate', 'expertInterviewEvent.expert'])
            ->when(isset($request) && !empty($request->fromDate), function (Builder $builder) use ($request)
            {
                $builder->where('start_date','>=' , $request->fromDate());
            })
            ->when(isset($request) && !empty($request->toDate), function (Builder $builder) use ($request)
            {
                $builder->where('start_date','<=' , $request->toDate());
            })
            ->when(isset($request) && !empty($request->status), function (Builder $builder) use ($request)
            {
                $builder->where('status', $request->status);
            })
            ->orderByDesc('start_date');
    }

    public function getUserEventsByDate(User $user, Carbon $date)
    {
        return UserEvent::query()
            ->with(['user', 'candidate', 'expertInterviewEvent.expert'])
            ->where('start_date','>=' , $date->startOfDay()->timestamp)
            ->where('start_date','<=' , $date->endOfDay()->timestamp)
            ->where([
                'status' => UserEvent::USER_EVENT_STATUS_APPROVED,
                'user_id' => $user->id
            ])
            ->orderByDesc('start_date')
            ->get();
    }

    public function createInterviewInvitationEvent(InterviewInvitationRequest $request, Candidate $candidate): UserEvent
    {
        $userEvent = new UserEvent();
        $user = $this->authService->getUser();
        $userEvent->user_id = $user->id;
        $userEvent->candidate_id = $candidate->id;
        $userEvent->start_date = $request->startDate();
        $userEvent->end_date = $request->endDate();
        $userEvent->description = $request->description;
        $userEvent->status = UserEvent::USER_EVENT_STATUS_PENDING;
        $userEvent->access_token_yes = Str::random(42);
        $userEvent->access_token_no = Str::random(42);

        $userEvent->save();

        return $userEvent;
    }

    public function getEventByAgreedToken(string $token): ?UserEvent
    {
        /** @var UserEvent $userEvent */
        $userEvent = UserEvent::query()
            ->where('access_token_yes', $token)
            ->where('status', UserEvent::USER_EVENT_STATUS_PENDING)
            ->first();

        return $userEvent;
    }

    public function getEventByCancelToken(string $token): ?UserEvent
    {
        /** @var UserEvent $userEvent */
        $userEvent = UserEvent::query()
            ->where('access_token_no', $token)
            ->where('status', UserEvent::USER_EVENT_STATUS_PENDING)
            ->first();

        return $userEvent;
    }
}
