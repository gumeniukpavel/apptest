<?php

namespace App\Db\Service;

use App\Db\Entity\Category;
use App\Db\Entity\Tariff;
use App\Db\Entity\Test;
use App\Db\Entity\User;
use App\Db\Entity\UserTariffCategory;

class UserTariffCategoryDao
{
    public function getCategoriesCountByUser(User $user): int
    {
        return UserTariffCategory::query()
            ->where([
                'user_id' => $user->id
            ])
            ->count();
    }

    public function getUserTariffCategory(User $user, int $categoryId)
    {
        return UserTariffCategory::query()
            ->where([
                'user_id' => $user->id,
                'category_id' => $categoryId
            ])
            ->exists();
    }

    public function createUserTariffCategory(User $user, int $categoryId, Tariff $tariff)
    {
        $entityExists = UserTariffCategory::query()->where([
            'user_id' => $user->id,
            'category_id' => $categoryId,
            'tariff_id' => $tariff->id
        ])->exists();

        if (!$entityExists)
        {
            $entity = new UserTariffCategory();
            $entity->user_id = $user->id;
            $entity->category_id = $categoryId;
            $entity->tariff_id = $tariff->id;
            $entity->save();
        }
    }
}
