<?php


namespace App\Db\Service;

use App\Db\Entity\Promotion\AffiliatedPerson;
use App\Db\Entity\Promotion\AffiliatedPersonStatistic;
use App\Db\Entity\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AffiliatedPersonStatisticDao
{
    public function getAffiliatedPersonStatistics($request)
    {
        $startDate = Carbon::now()->startOfMonth()->subMonth();
        $endDate = Carbon::now()->endOfMonth();

        if($request->fromDate && $request->toDate)
        {
            $startDate = Carbon::createFromTimestamp($request->fromDate)->startOfDay();
            $endDate = Carbon::createFromTimestamp($request->toDate)->endOfDay();
        }

        $statistics = DB::select(
            DB::raw("SELECT ap.id as ap_id, ap.name, j1.tariff, j1.userCount
                FROM affiliated_persons AS ap
                JOIN
                    (
                    SELECT a.id as ap_id, NULLIF(t.name , 'Без тарифа') as tariff, count(u.id) userCount
                    FROM affiliated_persons AS a
                    LEFT JOIN affiliated_person_statistics AS aps ON a.id = aps.affiliated_person_id
                    LEFT JOIN users AS u ON aps.user_id = u.id
                    LEFT JOIN tariffs AS t ON t.id = aps.tariff_id
                    WHERE aps.created_at <= :end
                        AND aps.created_at >= :from
                    GROUP BY a.id, t.name
                    ) j1
                ON ap.id = j1.ap_id
                ORDER BY ap.id, j1.tariff"),
            [
                'from' => $startDate,
                'end' =>  $endDate
            ]
        );

        return $statistics;
    }

    /**
     * @param User $user
     * @param AffiliatedPerson $affiliatedPerson
     * @return AffiliatedPersonStatistic
     */
    public function add(User $user, AffiliatedPerson $affiliatedPerson): AffiliatedPersonStatistic
    {
        $affiliatedPersonStatistic = new AffiliatedPersonStatistic();
        $affiliatedPersonStatistic->user_id = $user->id;
        $affiliatedPersonStatistic->affiliated_person_id = $affiliatedPerson->id;
        $affiliatedPersonStatistic->tariff_id = null;

        $affiliatedPersonStatistic->save();

        return $affiliatedPersonStatistic;
    }

    public function delete(AffiliatedPersonStatistic $affiliatedPersonStatistic)
    {
        $affiliatedPersonStatistic->delete();
    }

    public function addPromoCode($promoCode): void
    {
        Session::push('promo_code', $promoCode);
    }
}

