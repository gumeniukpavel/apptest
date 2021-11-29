<?php

namespace App\Http\Controllers\Api\Event;

use App\Db\Entity\Event;
use App\Db\Service\EventDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Event\GetListRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

use Carbon\Carbon;

class EventController extends BaseController
{
    protected EventDao $eventService;

    public function __construct(EventDao $eventDao, AuthService $authService)
    {
        parent::__construct($authService);
        $this->eventService = $eventDao;
    }

    public function list(GetListRequest $request)
    {
        $searchQuery = $this->eventService->getSearchQuery(
            $this->user(),
            $request
        );
        return $this->json(
            new PaginationResource($searchQuery, $request->page)
        );
    }

    public function actionGetOne(int $id)
    {
        $event = $this->eventService->getOne($id);

        if ($this->user()->cannot('view', $event))
        {
            return $this->responsePermissionsDenied();
        }
        return $this->json(
            $event
        );
    }

    public function actionGetPopupNotificationsList()
    {
        $events = $this->eventService->getPopupNotificationList($this->user()->id);

        return $this->json($events);
    }

    public function actionReadAllPopupNotifications()
    {
        $this->eventService->readAllPopupNotificationList($this->user()->id);

        return $this->json(trans('events.popupNotification.listEmpty'));
    }

    public function actionReadOnePopupNotifications(int $id)
    {
        /** @var Event $event */
        $event = $this->eventService->getOnePopupNotification($this->user()->id, $id);

        if ($this->user()->cannot('view', $event))
        {
            return $this->responsePermissionsDenied();
        }
        $event->is_read = true;
        $event->save();

        return $this->json();
    }
}
