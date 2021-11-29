<?php

namespace App\Http\Controllers\Api\User;

use App\Db\Entity\Payment;
use App\Db\Entity\Promotion\AffiliatedPersonStatistic;
use App\Db\Entity\UserProfile;
use App\Db\Service\AffiliatedPersonStatisticDao;
use App\Db\Service\PaymentDao;
use App\Db\Service\TariffUserDao;
use App\Db\Service\UserDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\User\GetListCountriesRequest;
use App\Http\Requests\User\GetUserTariffHistoryRequest;
use App\Http\Requests\User\GetUserTariffListRequest;
use App\Http\Requests\User\UpdateAmazonDataRequest;
use App\Http\Requests\User\GetListManagersRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\UpdateLegalInfoRequest;
use App\Http\Requests\User\UpdateNotificationInfoRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends BaseController
{
    private UserDao $userDao;

    public function __construct(
        UserDao $userDao,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->userDao = $userDao;
    }

    public function getCountryList(GetListCountriesRequest $request)
    {
        $localizations = App::getLocale();
        $countries = $this->userDao->getCountriesList($localizations);

        return $this->json(
            new PaginationResource($countries, $request->page, 100)
        );
    }

    public function updateNotificationInfo(UpdateNotificationInfoRequest $request)
    {
        $user = $this->user();
        $request->updateEntity($user);
        $user->save();
        return $this->json();
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $this->user();
        if (!Str::is($request->newPassword, $request->repeatPassword))
        {
            return $this->jsonError('Пароли не совпадают');
        }
        if (!Hash::check($request->oldPassword, $user->password))
        {
            return $this->jsonError('Старый пароль не действителен');
        }

        $user->password = $request->newPassword;
        $user->save();
        return $this->json();
    }

    public function updateCommonInfo(UpdateProfileRequest $request)
    {
        $user = $this->user();
        /** @var UserProfile $profile */
        $profile = $user->profile()->firstOrCreate([]); // User profile может быть не создан до этого момента
        $request->updateEntity($user, $profile);
        $user->save();
        $user->profile()->save($profile);
        $user->load('profile');
        return $this->json($user);
    }

    public function updateLegalInfo(UpdateLegalInfoRequest $request)
    {
        $user = $this->user();
        /** @var UserProfile $profile */
        $profile = $user->profile()->firstOrCreate([]); // User profile может быть не создан до этого момента
        $profile = $request->updateEntity($profile);
        $user->profile()->save($profile);
        return $this->json($user);
    }

    public function updateAmazonData(UpdateAmazonDataRequest $request)
    {
        $user = $this->user();
        $user = $request->updateEntity($user);
        $user->save();
        return $this->json($user);
    }

    public function actionGetManagersList(GetListManagersRequest $request)
    {
        $query = $this->userDao->listManagers();

        return $this->json(
            new PaginationResource($query, $request->getPage())
        );
    }

    public function userTariffList(GetUserTariffListRequest $request): JsonResponse
    {
        $query = $this->userDao->getUserTariffList($request);

        return $this->json(['items' => $query]);
    }

    public function userTariffHistory(GetUserTariffHistoryRequest $request): JsonResponse
    {
        $query = $this->userDao->getUserTariffHistory($request);

        return $this->json(['items' => $query]);
    }
}
