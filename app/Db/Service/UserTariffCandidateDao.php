<?php

namespace App\Db\Service;

use App\Db\Entity\Candidate;
use App\Db\Entity\Tariff;
use App\Db\Entity\User;
use App\Db\Entity\UserTariffCandidate;

class UserTariffCandidateDao
{
    public function getCandidatesCountByUser(User $user): int
    {
        return UserTariffCandidate::query()
            ->where([
                'user_id' => $user->id
            ])
            ->count();
    }

    public function createUserTariffCandidate(User $user, Candidate $candidate, Tariff $tariff)
    {
        $entityExists = UserTariffCandidate::query()->where([
            'user_id' => $user->id,
            'candidate_id' => $candidate->id,
            'tariff_id' => $tariff->id
        ])->exists();

        if (!$entityExists)
        {
            $entity = new UserTariffCandidate();
            $entity->user_id = $user->id;
            $entity->candidate_id = $candidate->id;
            $entity->tariff_id = $tariff->id;
            $entity->save();
        }
    }
}
