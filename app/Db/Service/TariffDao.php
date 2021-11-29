<?php

namespace App\Db\Service;

use App\Constant\AppMode;
use App\Db\Entity\PublicEntity\PublicTariff;
use App\Db\Entity\Tariff;
use App\Db\Entity\TariffPrice;
use App\Db\Entity\TariffUser;
use App\Db\Entity\User;
use App\Http\Requests\Payment\AddTransactionRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TariffDao
{
    private string $mode;

    public function __construct()
    {
        $this->mode = config('app.mode');
    }

    public function getOne(int $id): ?Tariff
    {
        /** @var Tariff $tariff */
        $tariff = Tariff::query()->where('id', $id)->first();
        return $tariff;
    }

    public function getListQuery(): Builder
    {
        if ($this->mode == AppMode::$Beta->getValue())
        {
            return Tariff::query()
                ->with('tariffPrices.tariffPeriod')
                ->where('id', Tariff::Beta);
        }
        elseif ($this->mode == AppMode::$Corporate->getValue())
        {
            return Tariff::query()
                ->with('tariffPrices.tariffPeriod')
                ->where('id', Tariff::Corporate);
        }
        else
        {
            return Tariff::query()
                ->with('tariffPrices.tariffPeriod')
                ->where('is_private', false);
        }
    }

    public function getPublicListQuery(): Builder
    {
        return PublicTariff::query()
            ->with('tariffPrices.tariffPeriod')
            ->where('is_private', false);
    }

    public function getTariffPriceById(Tariff $tariff, int $tariffPriceId): ?TariffPrice
    {
        /** @var TariffPrice $tariffPrice */
        $tariffPrice = TariffPrice::query()
            ->where([
                'id' => $tariffPriceId,
                'tariff_id' => $tariff->id
            ])->first();
        return $tariffPrice;
    }

    public function getLastTariffUserByUserId(int $userId): ?TariffUser
    {
        /** @var TariffUser $tariffUser */
        $tariffUser = TariffUser::query()
            ->where([
                'user_id' => $userId,
                'is_active' => true
            ])->first();

        return $tariffUser;
    }

    public function updateTariffUserByTransaction(TariffUser $tariffUser, AddTransactionRequest $request): ?TariffUser
    {
        /** @var TariffPrice $tariffPrice */
        $tariffPrice = TariffPrice::query()->where([
            'tariff_id' => $tariffUser->tariff->id,
            'tariff_period_id' => $tariffUser->tariffPeriod->id
        ])->first();
        if ($tariffPrice)
        {
            $endedAt = Carbon::createFromTimestamp($tariffUser->ended_at);
            $newEndedAt = Carbon::createFromTimestamp($tariffUser->ended_at)
                ->addMonths($tariffUser->tariffPeriod->months);
            $periodDays = $endedAt->diffInDays($newEndedAt);
            if ($periodDays <= 0)
            {
                return null;
            }
            $pricePerDay = $tariffPrice->price / $periodDays;
            if ($pricePerDay <= 0)
            {
                return null;
            }
            $paidDays = ceil($request->sum / $pricePerDay);
            $tariffUser->ended_at = $endedAt->addDays((int)$paidDays)->timestamp;
            $tariffUser->save();

            return $tariffUser;
        }
        else
        {
            return null;
        }
    }

    public function create(string $name, string $description): Tariff
    {
        $tariff = Tariff::create([
            'name' => $name,
            'description' => $description,
        ]);

        return $tariff;
    }
}
