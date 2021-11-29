<?php

namespace App\Db\Service;

use App\Constant\OrderType;
use App\Db\Entity\Payment;
use App\Db\Entity\Promotion\AffiliatedPersonStatistic;
use App\Db\Entity\Tariff;
use App\Db\Entity\TariffPeriod;
use App\Db\Entity\TariffPrice;
use App\Db\Entity\TariffUser;
use App\Db\Entity\User;
use App\Http\Requests\Payment\ChangeStatusRequest;
use App\Http\Requests\Payment\GetListStatisticRequest;
use Carbon\Carbon;
use App\Http\Requests\Payment\GetListRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PaymentDao
{
    public function getOne(int $id): ?Payment
    {
        /** @var Payment $payment */
        $payment = Payment::query()->with(['tariff', 'tariffPeriod'])
            ->where('id', $id)->first();
        return $payment;
    }

    public function getAllList(): Builder
    {
        return Payment::query()->with(['tariff', 'tariffPeriod']);
    }

    public function getSearchQuery(User $user, GetListRequest $request): Builder
    {
        $builder = $this->getFilteredListQuery($request);
        return $builder->where('user_id', $user->id);
    }

    private function getFilteredListQuery(GetListRequest $request): Builder
    {
        $builder = Payment::query()
            ->with(['tariff', 'tariffPeriod'])
            ->when(isset($request) && !empty($request->searchString), function (Builder $builder) use ($request)
            {
                $builder->whereRaw('UPPER(`description`) LIKE ?', [mb_strtoupper('%' . $request->searchString . '%', 'UTF-8')]);
            })
            ->when(isset($request) && !empty($request->getFromDate()), function (Builder $builder) use ($request)
            {
                $builder->where('updated_at','>=' , $request->getFromDate());
            })
            ->when(isset($request) && !empty($request->getToDate()), function (Builder $builder) use ($request)
            {
                $builder->where('updated_at','<=' , $request->getToDate());
            })
            ->when(isset($request) && !empty($request->status), function (Builder $builder) use ($request)
            {
                $builder->where('status', $request->status);
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
            }
        }
        else
        {
            $builder->orderByDesc('created_at');
        }

        return $builder;
    }

    public function createPayment(User $user, Tariff $tariff, TariffPrice $tariffPrice)
    {
        $payment = new Payment();
        $payment->user_id = $user->id;
        $payment->tariff_id = $tariff->id;
        $payment->total = $tariffPrice->price;
        $payment->tariff_period_id = $tariffPrice->tariffPeriod->id;
        $payment->status = Payment::PAYMENT_STATUS_PENDING;

        if ($payment->tariff_id == Tariff::EconomyTariff)
        {
            if (!$this->isCanPayEconomyTariff($user))
            {
                return false;
            }
        }
        $payment->save();

       /** @var AffiliatedPersonStatistic $affiliatedPersonStatistics */
        $affiliatedPersonStatistic = AffiliatedPersonStatistic::query()
            ->where('user_id', $user->id)
            ->whereNull('tariff_id')
            ->first();

        /** @var AffiliatedPersonStatistic[] $affiliatedPersonStatisticTariffCount */
        $affiliatedPersonStatisticTariffCount = AffiliatedPersonStatistic::query()
            ->where('user_id', $user->id)
            ->whereNotNull('tariff_id')
            ->count();

        if ($affiliatedPersonStatistic && $affiliatedPersonStatisticTariffCount == 0)
        {
            $newAffiliatedPersonStatistic = new AffiliatedPersonStatistic();
            $newAffiliatedPersonStatistic->user_id = $user->id;
            $newAffiliatedPersonStatistic->tariff_id = $tariff->id;
            $newAffiliatedPersonStatistic->affiliated_person_id = $affiliatedPersonStatistic->affiliated_person_id;

            $newAffiliatedPersonStatistic->save();
        }

        return $payment;
    }

    public function changePaymentStatus(Payment $payment, ChangeStatusRequest $request)
    {
        $payment->notes = $request->notes;
        $payment->transaction_number = $request->transactionNumber;
        $payment->status = $request->status;

        $payment->save();

        return $payment;
    }

    public function callbackPayment(Payment $payment)
    {
        $payment->status = Payment::PAYMENT_STATUS_COMPLETED;
        $payment->save();

        TariffUser::query()
            ->where('user_id', $payment->user_id)
            ->update([
                'is_active' => false
            ]);

        $tariffUser = new TariffUser();
        $tariffUser->user_id = $payment->user_id;
        $tariffUser->tariff_id = $payment->tariff_id;
        $tariffUser->tariff_period_id = $payment->tariffPeriod->id;
        $tariffUser->is_active = true;
        if ($payment->tariff_id == Tariff::EconomyTariff)
        {
            $tariffUser->ended_at = Carbon::now()->addMonths(1)->timestamp;
        }
        else
        {
            $tariffUser->ended_at = Carbon::now()->addMonths($payment->tariffPeriod->months)->timestamp;
        }
        $tariffUser->save();
    }

    public function isCanPayEconomyTariff(User $user): bool
    {
        $paymentUser = Payment::query()
            ->where([
                'user_id' => $user->id,
                'tariff_id' => Tariff::EconomyTariff,
                'status' => Payment::PAYMENT_STATUS_COMPLETED
            ])
            ->exists();
        return !$paymentUser;
    }

    public function cancelPayment(Payment $payment): Payment
    {
       $payment->status = Payment::PAYMENT_STATUS_CANCELED;
       $payment->save();

       return $payment;
    }

    public function getPaymentStatisticQuery(
        User $user,
        GetListStatisticRequest $request): \Illuminate\Database\Query\Builder
    {
        return DB::table(Tariff::tableName() . ' AS t')
            ->select([
                't.id',
                't.name'
            ])
            ->addSelect([
                'threeMonths' => function($query) use ($request, $user)
                {
                    $query->select(DB::raw('count(payments.tariff_id)'))
                        ->from('payments')
                        ->join('tariffs', 'tariffs.id', 'payments.tariff_id')
                        ->whereColumn('tariff_id', 't.id')
                        ->where([
                            'status' => Payment::PAYMENT_STATUS_COMPLETED,
                            'tariff_period_id' => TariffPeriod::TARIFF_PERIOD_THREE_MONTHS
                        ])
                        ->when(isset($request) && isset($request->fromDate), function (\Illuminate\Database\Query\Builder $builder) use ($request)
                        {
                            $builder->where('payments.updated_at', '>=', Carbon::createFromTimestamp($request->fromDate));
                        })
                        ->when(isset($request) && isset($request->toDate), function (\Illuminate\Database\Query\Builder $builder) use ($request)
                        {
                            $builder->where('payments.updated_at', '<=', Carbon::createFromTimestamp($request->toDate));
                        })
                        ->groupBy('t.id');
                },
                'sixMonths' => function($query) use ($request, $user)
                {
                    $query->select(DB::raw('count(payments.tariff_id)'))
                        ->from('payments')
                        ->join('tariffs', 'tariffs.id', 'payments.tariff_id')
                        ->whereColumn('tariff_id', 't.id')
                        ->where([
                            'status' => Payment::PAYMENT_STATUS_COMPLETED,
                            'tariff_period_id' => TariffPeriod::TARIFF_PERIOD_SIX_MONTHS
                        ])
                        ->when(isset($request) && isset($request->fromDate), function (\Illuminate\Database\Query\Builder $builder) use ($request)
                        {
                            $builder->where('payments.updated_at', '>=', Carbon::createFromTimestamp($request->fromDate));
                        })
                        ->when(isset($request) && isset($request->toDate), function (\Illuminate\Database\Query\Builder $builder) use ($request)
                        {
                            $builder->where('payments.updated_at', '<=', Carbon::createFromTimestamp($request->toDate));
                        })
                        ->groupBy('t.id');
                },
                'twelveMonths' => function($query) use ($request, $user)
                {
                    $query->select(DB::raw('count(payments.tariff_id)'))
                        ->from('payments')
                        ->join('tariffs', 'tariffs.id', 'payments.tariff_id')
                        ->whereColumn('tariff_id', 't.id')
                        ->where([
                            'status' => Payment::PAYMENT_STATUS_COMPLETED,
                            'tariff_period_id' => TariffPeriod::TARIFF_PERIOD_TWELVE_MONTHS
                        ])
                        ->when(isset($request) && isset($request->fromDate), function (\Illuminate\Database\Query\Builder $builder) use ($request)
                        {
                            $builder->where('payments.updated_at', '>=', Carbon::createFromTimestamp($request->fromDate));
                        })
                        ->when(isset($request) && isset($request->toDate), function (\Illuminate\Database\Query\Builder $builder) use ($request)
                        {
                            $builder->where('payments.updated_at', '<=', Carbon::createFromTimestamp($request->toDate));
                        })
                        ->groupBy('t.id');
                }
            ]);
    }
}
