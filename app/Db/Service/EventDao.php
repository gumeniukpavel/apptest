<?php

namespace App\Db\Service;

use App\Constant\OrderType;
use App\Db\Entity\Event;
use App\Db\Entity\ProjectCandidate;
use App\Db\Entity\User;
use App\Http\Requests\Event\GetListRequest;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventDao
{
    public function getOne(int $id): ?Event
    {
        /** @var Event $event */
        $event = Event::query()->where('id', $id)->first();
        return $event;
    }

    public function getSearchQuery(User $user, GetListRequest $request): Builder
    {
        $builder = $this->getFilteredListQuery($request);
        return $builder->where('user_id', $user->id);
    }

    private function getFilteredListQuery(GetListRequest $request): Builder
    {
        $builder = Event::query()
            ->when(isset($request) && !empty($request->searchString), function (Builder $builder) use ($request)
            {
                $builder->whereRaw('UPPER(`description`) LIKE ?', [mb_strtoupper('%' . $request->searchString . '%', 'UTF-8')]);
            })
            ->when(isset($request) && !empty($request->eventType), function (Builder $builder) use ($request)
            {
                $builder->where('event_type', $request->eventType);
            })
            ->when(isset($request) && !empty($request->getFromDate()), function (Builder $builder) use ($request)
            {
                $builder->where('created_at','>=' , $request->getFromDate());
            })
            ->when(isset($request) && !empty($request->getToDate()), function (Builder $builder) use ($request)
            {
                $builder->where('created_at','<=', $request->getToDate());
            });

        if ($request->orderType)
        {
            switch ($request->orderType)
            {
                case OrderType::$CreatedAtAsc->getValue():
                    $builder->orderBy('created_at', 'asc');
                    break;

                case OrderType::$CreatedAtDesc->getValue():
                    $builder->orderBy('created_at', 'desc');
                    break;

                case OrderType::$NameAsc->getValue():
                    $builder->orderBy('description', 'asc');
                    break;

                case OrderType::$NameDesc->getValue():
                    $builder->orderBy('description', 'desc');
                    break;
            }
        }
        else
        {
            $builder->orderByDesc('created_at');
        }

        return $builder;
    }

    public function createEvent(string $eventType, string $subType, int $userId, int $param1 = null, int $param2 = null, int $param3 = null)
    {
        return Event::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'sub_type' => $subType,
            'param_1' => $param1,
            'param_2' => $param2,
            'param_3' => $param3,
        ]);
    }

    public function markEventAsDeleted(int $objectId, string $type)
    {
        /** @var Event[] $objectEvents */
        $objectEvents = Event::query()
            ->where([
                'event_type' => $type,
                'param_1' => $objectId
            ])->get();

        foreach ($objectEvents as $objectEvent)
        {
            $objectEvent->object_is_deleted = true;
            $objectEvent->save();
        }
    }

    public function getPopupNotificationList(int $userId)
    {
        return Event::query()
            ->where([
                'user_id' => $userId,
                'is_popup_notification' => true,
                'is_read' => false
            ])->orderBy('created_at', 'desc')->limit(30)->get();
    }

    public function getOnePopupNotification(int $userId, int $id)
    {
        return Event::query()
            ->where([
                'id' => $id,
                'user_id' => $userId,
                'is_popup_notification' => true,
                'is_read' => false
            ])->first();
    }

    public function readAllPopupNotificationList(int $userId)
    {
        Event::query()
            ->where([
                'user_id' => $userId,
                'is_popup_notification' => true,
                'is_read' => false
            ])
            ->update([
                'is_read' => true
            ]);
    }
}
