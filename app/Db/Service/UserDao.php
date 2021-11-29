<?php

namespace App\Db\Service;

use App\Constant\Localization;
use App\Constant\OrderType;
use App\Db\Entity\Country;
use App\Db\Entity\Payment;
use App\Db\Entity\Role;
use App\Db\Entity\Tariff;
use App\Db\Entity\TariffPeriod;
use App\Db\Entity\TariffUser;
use App\Db\Entity\User;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isTrue;

class UserDao
{
    public function getByApiToken(string $token): ?User
    {
        /** @var User $user */
        $user = User::query()
            ->select('users.*')
            ->join('user_auth_tokens', function (JoinClause $join) use ($token) {
                $join->on('user_auth_tokens.user_id', '=', 'users.id')
                    ->where('user_auth_tokens.token', $token);
            })->first();
        return $user;
    }

    public function removeCurrentApiToken(User $user, string $token)
    {
        $user->authTokens()
            ->where('token', $token)
            ->delete();
    }

    public function firstWithData(int $id) : ?User
    {
        /** @var User $user */
        $user = User::with('profile')
            ->where('id', $id)
            ->first();
        return $user;
    }

    public function listManagers()
    {
        return User::with('profile')
            ->where('role_id', Role::ROLE_CUSTOMER);
    }

    public function getCountriesList(string $localizations)
    {
        return Country::query()
            ->when($localizations == Localization::$En->getValue(), function ($query) {
                $query->select(['id', 'en_name as name'])
                    ->orderBy('en_name');
            })
            ->when($localizations == Localization::$Ua->getValue(), function ($query) {
                $query->select(['id', 'ua_name as name'])
                    ->orderBy('ua_name');
            })
            ->when($localizations == Localization::$Ru->getValue(), function ($query) {
                $query->select(['id', 'ru_name as name'])
                    ->orderBy('ru_name');
            });
    }

    public function setEconomTariffUser(User $user)
    {
        $tariffUser = new TariffUser();
        $tariffUser->user_id = $user->id;
        $tariffUser->tariff_id = Tariff::EconomyTariff;
        $tariffUser->tariff_period_id = TariffPeriod::TARIFF_PERIOD_ONE_MONTH;
        $tariffUser->is_active = true;
        $tariffUser->ended_at = Carbon::now()->addDays(15)->timestamp;
        $tariffUser->save();
    }

    public function setPremiumTariffUser(User $user)
    {
        $tariffUser = new TariffUser();
        $tariffUser->user_id = $user->id;
        $tariffUser->tariff_id = Tariff::Premium2;
        $tariffUser->tariff_period_id = TariffPeriod::TARIFF_PERIOD_THREE_MONTHS;
        $tariffUser->is_active = true;
        $tariffUser->ended_at = Carbon::now()->addMonths(3)->timestamp;
        $tariffUser->save();
    }

    public function getUserTariffList($request)
    {
        $userTariffList = [];

        $query = "select
            distinct(u.id),
            CONCAT(u.name, u.middle_name) as fullName,
            up.organization_name as company,
            t2.name as tariff,
            tp.months as period,
            j.created_at as activeDate,
            from_unixtime(j.ended_at, '%Y-%m-%d %h:%m:%s') as deActiveDate,
            j.is_active as isPaid,
            j.status as status
            from users as u
            join user_profiles as up on u.id = up.user_id
            join payments as p2 on u.id = p2.user_id
            join tariffs as t2 on p2.tariff_id = t2.id
            join tariff_periods as tp on p2.tariff_period_id = tp.id
            join
            (
                select user_id, tariff_id as actualTarifId, created_at, status, ended_at, is_active
                from tariff_users as tu2
                join
                (
                    select MAX(tu.id) as maxId
                    from  tariff_users  as tu
                    group by tu.user_id
                ) as tu1 on tu2.id = tu1.maxId
            ) as j on j.user_id = u.id and j.actualTarifId = t2.id";

        if ($request->tariffId)
        {
            $query = $query." WHERE t2.id = $request->tariffId";
        }
        if ($request->status)
        {
            if (!$request->tariffId)
            {
                $query = $query." WHERE j.status = '".  $request->status. "'";
            }
            else
            {
                $query = $query." AND j.status = '".  $request->status. "'";
            }
        }
        if ($request->activeDateFrom && $request->activeDataTo)
        {
            $activeDateFrom = Carbon::createFromTimestamp($request->activeDateFrom);
            $activeDateTo = Carbon::createFromTimestamp($request->activeFromTo);

            if (!($request->status && $request->tariffId)
                || !$request->status
                || !$request->tariffId
            )
            {
                $query = $query." WHERE j.activeDate <= $activeDateTo AND j.activeDate >= $activeDateFrom";
            }
            else
            {
                $query = $query." AND j.activeDate <= $activeDateTo AND j.activeDate >= $activeDateFrom";
            }
        }
        if ($request->deActiveDateFrom && $request->deActiveDataTo)
        {
            $deActiveDateFrom = $request->deActiveDateFrom;
            $deActiveDataTo = $request->deActiveDataTo;

            if (!($request->status && $request->tariffId && $request->activeDateFrom && $request->activeDataTo)
                || !($request->status && $request->tariffId)
                || !($request->status)
                || !($request->tariffId)
                || !($request->activeDateFrom && $request->activeDataTo)
                || !($request->status && $request->activeDateFrom && $request->activeDataTo)
                || !($request->tariffId && $request->activeDateFrom && $request->activeDataTo)
            )
            {
                $query = $query." WHERE j.deActiveDate <= $deActiveDataTo AND j.deActiveDate >= $deActiveDateFrom";
            }
            else
            {
                $query = $query." AND j.deActiveDate <= $deActiveDataTo AND j.deActiveDate >= $deActiveDateFrom";
            }
        }

        if ($request->orderType)
        {
            $orderType = $request->orderType;
            $query = $query." ORDER BY ";
            if ($orderType == OrderType::$NameAsc->getValue())
            {
                $query = $query."fullName ASC";
            }
            elseif ($orderType == OrderType::$NameDesc->getValue())
            {
                $query = $query."fullName DESC";
            }
            elseif ($orderType == OrderType::$CreatedAtAsc->getValue())
            {
                $query = $query."j.activeDate ASC";
            }
            else
            {
                $query = $query."j.activeDate DESC";
            }
        }

        return DB::select(
            DB::raw($query)
        );
    }

    public function getUserTariffHistory($request)
    {
        $query = "select t.name as tariff,
            tp.months as period,
            tu.created_at as activeDate,
            from_unixtime( tu.ended_at, '%Y-%m-%d %h:%m:%s') as deActiveDate,
            p.created_at as paymentDate,
            tu.status as status
            from tariff_users as tu
            join tariffs as t on tu.tariff_id = t.id
            join payments as p on p.user_id = $request->userId and p.tariff_id = tu.tariff_id
            join tariff_periods as tp on p.tariff_period_id = tp.id
            where tu.user_id = $request->userId";

        if ($request->tariffId)
        {
            $query = $query." AND t.id = $request->tariffId";
        }
        if ($request->status)
        {
            $query = $query." AND tu.status = '".  $request->status. "'";
        }
        if ($request->activeDateFrom && $request->activeFromTo)
        {
            $activeDateFrom = Carbon::createFromTimestamp($request->activeDateFrom);
            $activeDateTo = Carbon::createFromTimestamp($request->activeFromTo);

            $query = $query." AND activeDate <= $activeDateTo AND activeDate >= $activeDateFrom";
        }
        if ($request->deActiveDateFrom && $request->deActiveFromTo)
        {
            $deActiveDateFrom = Carbon::createFromTimestamp($request->deActiveDateFrom);
            $deActiveDateTo = Carbon::createFromTimestamp($request->deActiveFromTo);

            $query = $query." AND deActiveDate <= $deActiveDateTo AND deActiveDate >= $deActiveDateFrom";
        }
        if ($request->paymentDateFrom && $request->paymentFromTo)
        {
            $paymentDateFrom = Carbon::createFromTimestamp($request->paymentDateFrom);
            $paymentDateTo = Carbon::createFromTimestamp($request->paymentFromTo);

            $query = $query." AND paymentDate <= $paymentDateTo AND paymentDate >= $paymentDateFrom";
        }

        if ($request->orderType)
        {
            $orderType = $request->orderType;
            $query = $query." ORDER BY ";
            if ($orderType == OrderType::$NameAsc->getValue())
            {
                $query = $query."tariff ASC";
            }
            elseif ($orderType == OrderType::$NameDesc->getValue())
            {
                $query = $query."tariff DESC";
            }
            elseif ($orderType == OrderType::$CreatedAtAsc->getValue())
            {
                $query = $query."j.activeDate ASC";
            }
            else
            {
                $query = $query."j.activeDate DESC";
            }
        }

        return  DB::select(
            DB::raw($query)
        );
    }
}
