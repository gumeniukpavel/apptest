<?php

namespace App\Http\Controllers\Api\NonAuthorized;

use App\Constant\Localization;
use App\Constant\OrderType;
use App\Db\Entity\Category;
use App\Db\Entity\Event;
use App\Db\Entity\LetterTemplateType;
use App\Db\Entity\Level;
use App\Db\Entity\ProjectStatus;
use App\Db\Entity\Specialization;
use App\Db\Entity\TariffUser;
use App\Db\Service\PaymentDao;
use App\Db\Service\UserDao;
use App\Http\Controllers\BaseController;
use App\Service\AuthService;

class HomeController extends BaseController
{
    private UserDao $userDao;
    private PaymentDao $paymentDao;

    public function __construct(
        UserDao $userDao,
        AuthService $authService,
        PaymentDao $paymentDao
    )
    {
        parent::__construct($authService);
        $this->userDao = $userDao;
        $this->paymentDao = $paymentDao;
    }

    public function initData()
    {
        $currentUser = $this->isLoggedIn() ? $this->userDao->firstWithData($this->user()->id) : null;

        $numberUnreadNotification = Event::query()
            ->where([
            'user_id' => $this->user()->id,
            'is_popup_notification' => true,
            'is_read' => false
        ])->count();

        if ($currentUser && !$currentUser->isBookkeeper())
        {
            /** @var TariffUser $tariffUser */
            $tariffUser = TariffUser::query()->where([
                'user_id' => $this->user()->id,
                'is_active' => true
            ])->with(['tariff', 'tariffPeriod'])->first();
        }
        else
        {
            $tariffUser = null;
        }
        return $this->json([
            'categories' => Category::all(),
            'projectStatuses' => ProjectStatus::all(),
            'levels' => Level::all(),
            'mailTemplateTypes' => LetterTemplateType::all(),
            'orderTypes' => OrderType::toArray(),
            'user' => $currentUser,
            'role' => $currentUser ? $currentUser->role->name : null,
            'specializations' => Specialization::all(),
            'userCurrentTariff' => $tariffUser,
            'currentMode' => config('app.mode'),
            'isCanPayEconomyTariff' => $currentUser ? $this->paymentDao->isCanPayEconomyTariff($currentUser) : null,
            'numberUnreadNotification' => $numberUnreadNotification,
            'locale' => Localization::toArray()
        ]);
    }
}
