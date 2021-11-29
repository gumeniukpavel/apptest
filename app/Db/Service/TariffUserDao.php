<?php

namespace App\Db\Service;

use App\Db\Entity\Tariff;
use App\Db\Entity\TariffPeriod;
use App\Db\Entity\TariffUser;
use App\Db\Entity\User;
use Carbon\Carbon;

class TariffUserDao
{
    public function getActiveByUser(User $user): ?TariffUser
    {
        /** @var TariffUser $tariffUser */
        $tariffUser = TariffUser::query()
            ->where([
                'user_id' => $user->id,
                'is_active' => 1
            ])
            ->first();

        return $tariffUser;
    }

    public function changeTariffForUser(User $user, Tariff $tariff)
    {
        TariffUser::query()
            ->where([
                'user_id' => $user->id,
                'is_active' => 1
            ])
            ->update(['is_active' => false]);

        $tariffUser = new TariffUser();
        $tariffUser->user_id = $user->id;
        $tariffUser->tariff_id = $tariff->id;
        $tariffUser->tariff_period_id = TariffPeriod::TARIFF_PERIOD_ONE_MONTH;
        $tariffUser->is_active = true;
        $tariffUser->ended_at = Carbon::now()->addMonth()->timestamp;
        $tariffUser->save();
    }

    public function cancelTariff(User $user): void
    {
        /** @var  TariffUser $tariffUser */
        $tariffUser = TariffUser::query()
            ->where([
                'user_id' => $user->id,
                'is_active' => 1
            ])->first();

        $tariffUser->is_active = false;
        $tariffUser->save();
    }

    public function pauseTariff(TariffUser $tariffUser)
    {
        $tariffUser->is_pause = true;
        $tariffUser->is_active = false;
        $tariffUser->paused_at = Carbon::now()->timestamp;
        $tariffUser->save();

        return $tariffUser;
    }

    public function unPauseTariff(TariffUser $tariffUser)
    {
        $tariffPausedAt = Carbon::createFromTimestamp($tariffUser->paused_at);
        $tariffEndedAt = Carbon::createFromTimestamp($tariffUser->ended_at);
        $unPausedAt = Carbon::now();
        $addTime = $tariffPausedAt->diff($unPausedAt);
        $endedAt = $tariffEndedAt->add($addTime);

        $tariffUser->is_pause = false;
        $tariffUser->is_active = true;
        $tariffUser->un_paused_at = $unPausedAt->timestamp;
        $tariffUser->ended_at = $endedAt->timestamp;
        $tariffUser->save();

        return $tariffUser;
    }
}
