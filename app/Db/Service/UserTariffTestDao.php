<?php

namespace App\Db\Service;

use App\Db\Entity\Tariff;
use App\Db\Entity\Test;
use App\Db\Entity\User;
use App\Db\Entity\UserTariffTest;

class UserTariffTestDao
{
    public function getTestsCountByUser(User $user): int
    {
        return UserTariffTest::query()
            ->where([
                'user_id' => $user->id
            ])
            ->count();
    }

    public function getUserTariffTest(User $user, Test $test)
    {
        return UserTariffTest::query()
            ->where([
                'user_id' => $user->id,
                'test_id' => $test->id
            ])
            ->exists();
    }

    public function createUserTariffTest(User $user, Test $test, Tariff $tariff)
    {

        $entityExists = UserTariffTest::query()->where([
            'user_id' => $user->id,
            'test_id' => $test->id,
            'tariff_id' => $tariff->id
        ])->exists();

        if (!$entityExists)
        {
            $entity = new UserTariffTest();
            $entity->user_id = $user->id;
            $entity->test_id = $test->id;
            $entity->tariff_id = $tariff->id;
            $entity->save();
        }
    }
}
