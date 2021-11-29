<?php

namespace App\Db\Service;

use App\Db\Entity\Candidate;
use App\Db\Entity\Expert;
use App\Db\Entity\StaffLevel;
use App\Db\Entity\User;
use App\Http\Requests\StaffLevel\GetListRequest;

class StaffLevelDao
{
    public function searchByNameAndUser(string $staffLevelName, User $user): ?StaffLevel
    {
        /** @var StaffLevel $staffLevel */
        $staffLevel = StaffLevel::query()
            ->whereRaw('UPPER(`name`) LIKE ?', [mb_strtoupper('%' . $staffLevelName . '%')])
            ->where('user_id', $user->id)
            ->first();

        return $staffLevel;
    }

    public function search(GetListRequest $request, User $user)
    {
        /** @var StaffLevel[] $staffLevel */
        $staffLevel = StaffLevel::query()
            ->whereRaw('UPPER(`name`) LIKE ?', [mb_strtoupper('%' . $request->searchString . '%', 'UTF-8')])
            ->where('user_id', $user->id)
            ->get();

        return $staffLevel;
    }

    public function createStaffLevelByUser(string $staffLevelName, User $user): StaffLevel
    {
        $staffLevel = new StaffLevel();
        $staffLevel->name = $staffLevelName;
        $staffLevel->user_id = $user->id;
        $staffLevel->save();

        return $staffLevel;
    }

    public function saveCandidateStaffLevel(Candidate $candidate, StaffLevel $staffLevel)
    {
        $candidate->staff_level_id = $staffLevel->id;
        $candidate->save();
    }

    public function saveExpertStaffLevel(Expert $expert, StaffLevel $staffLevel)
    {
        $expert->staff_level_id = $staffLevel->id;
        $expert->save();
    }
}
