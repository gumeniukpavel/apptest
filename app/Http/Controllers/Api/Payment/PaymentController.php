<?php

namespace App\Http\Controllers\Api\Payment;

use App\Db\Entity\Event;
use App\Db\Entity\Payment;
use App\Db\Entity\Tariff;
use App\Db\Entity\User;
use App\Db\Service\EventDao;
use App\Db\Service\PaymentDao;
use App\Db\Service\TariffDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Payment\AddTransactionRequest;
use App\Http\Requests\Payment\CancelPaymentRequest;
use App\Http\Requests\Payment\ChangeStatusRequest;
use App\Http\Requests\Payment\CreateRequest;
use App\Http\Requests\Payment\GetListRequest;
use App\Http\Requests\Payment\GetListStatisticRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;

class PaymentController extends BaseController
{
    private string $mode;
    private string $env;
    protected PaymentDao $paymentService;
    protected TariffDao $tariffService;
    protected EventDao $eventService;

    public function __construct(PaymentDao $paymentDao, TariffDao $tariffDao, EventDao $eventService, AuthService $authService)
    {
        parent::__construct($authService);
        $this->paymentService = $paymentDao;
        $this->tariffService = $tariffDao;
        $this->eventService = $eventService;
        $this->mode = config('app.mode');
        $this->env = config('app.env');
    }

    public function list(GetListRequest $request)
    {
        $searchQuery = $this->paymentService->getSearchQuery(
            $this->user(),
            $request
        );
        return $this->json(
            new PaginationResource($searchQuery, $request->page)
        );
    }

    public function allPaymentList(GetListRequest $request)
    {
        $query = $this->paymentService->getAllList();

        return $this->json(
            new PaginationResource($query, $request->page)
        );
    }

    public function create(CreateRequest $request)
    {
        if ($this->env == 'production')
        {
            return $this->jsonError();
        }

        if ($request->tariffId == Tariff::Beta || $request->tariffId == Tariff::Corporate)
        {
            return $this->responseNotFound('Тариф недоступен');
        }

        $tariff = $this->tariffService->getOne($request->tariffId);
        $tariffPrice = $this->tariffService->getTariffPriceById($tariff, $request->tariffPriceId);

        if (!$tariff || !$tariffPrice) {
            return $this->responseNotFound('Тариф не найден');
        } else {
            $payment = $this->paymentService->createPayment($this->user(), $tariff, $tariffPrice);
            if ($payment)
            {
                $this->eventService->createEvent(Event::EVENT_TYPE_PAYMENT, Event::EVENT_SUB_TYPE_CREATE, $this->user()->id, $payment->id, $tariff->id);
                return $this->json(
                    $payment
                );
            }
            else
            {
               return $this->jsonError();
            }
        }
    }

    public function changePaymentStatus(ChangeStatusRequest $request)
    {
        /** @var Payment $payment */
        $payment = Payment::query()->where('id', $request->paymentId)->first();

        if (!$payment || $this->user()->cannot('update', Payment::class))
        {
            return $this->responsePermissionsDenied();
        }

        if ($payment->status == Payment::PAYMENT_STATUS_PENDING)
        {
            $this->paymentService->changePaymentStatus($payment, $request);
        }
        else
        {
            return $this->jsonError();
        }

        return $payment;
    }

    //TODO
    public function callback($request)
    {
        $payment = $this->paymentService->getOne($request->payment_id);

        if (!$payment) {
            return $this->responseNotFound('Платеж не найден');
        } else {
            $this->paymentService->callbackPayment($payment);
            $this->eventService->createEvent(Event::EVENT_TYPE_PAYMENT, Event::EVENT_SUB_TYPE_UPDATE, $this->user()->id, $payment->id, $payment->tariff->id);

            return $this->json();
        }
    }

    //TODO: Delete this action
    public function actionFakeBuy(CreateRequest $request)
    {
        if ($this->env != 'development' && $this->env != 'local')
        {
            return $this->jsonError();
        }

        $tariff = $this->tariffService->getOne($request->tariffId);
        $tariffPrice = $this->tariffService->getTariffPriceById($tariff, $request->tariffPriceId);

        $payment = $this->paymentService->createPayment($this->user(), $tariff, $tariffPrice);
        if ($payment)
        {
            $this->paymentService->callbackPayment($payment);
            $this->eventService->createEvent(Event::EVENT_TYPE_PAYMENT, Event::EVENT_SUB_TYPE_CREATE, $this->user()->id, $payment->id, $tariff->id);
            return $this->json(
                $payment
            );
        }
        else
        {
            return $this->jsonError();
        }
    }

    public function actionAddTransaction(AddTransactionRequest $request)
    {
        $tariffUser = $this->tariffService->getLastTariffUserByUserId($request->userId);

        if (!$tariffUser)
        {
            return $this->jsonError();
        }

        $tariffUser = $this->tariffService->updateTariffUserByTransaction($tariffUser, $request);

        return $this->json(
            $tariffUser
        );
    }

    public function actionCancelPayment(CancelPaymentRequest $request)
    {
        /** @var Payment $payment */
        $payment = Payment::byId($request->paymentId);

        $this->paymentService->cancelPayment($payment);

        return $this->json();
    }

    public function actionGetStatistic(GetListStatisticRequest $request)
    {
        $statisticQuery = $this->paymentService->getPaymentStatisticQuery(
            $this->user(),
            $request
        );

        return $this->json(
            new PaginationResource($statisticQuery, $request->getPage())
        );
    }
}
