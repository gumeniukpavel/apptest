<?php

namespace App\Db\Service;

use App\Db\Entity\Candidate;
use App\Db\Entity\Expert;
use App\Db\Entity\StaffSpecialization;
use App\Db\Entity\User;
use App\Http\Requests\StaffSpecialization\GetListRequest;

class StaffSpecializationDao
{
    public function searchByNameAndUser(string $staffLevelName, User $user): ?StaffSpecialization
    {
        /** @var StaffSpecialization $staffSpecialization */
        $staffSpecialization = StaffSpecialization::query()
            ->whereRaw('UPPER(`name`) LIKE ?', [mb_strtoupper('%' . $staffLevelName . '%')])
            ->where('user_id', $user->id)
            ->first();

        return $staffSpecialization;
    }

    public function search(GetListRequest $request, User $user)
    {
        /** @var StaffSpecialization[] $staffSpecialization */
        $staffSpecialization = StaffSpecialization::query()
            ->whereRaw('UPPER(`name`) LIKE ?', [mb_strtoupper('%' . $request->searchString . '%', 'UTF-8')])
            ->where('user_id', $user->id)
            ->get();

        return $staffSpecialization;
    }

    public function createStaffSpecializationByUser(string $staffLevelName, User $user): StaffSpecialization
    {
        $staffSpecialization = new StaffSpecialization();
        $staffSpecialization->name = $staffLevelName;
        $staffSpecialization->user_id = $user->id;
        $staffSpecialization->save();

        return $staffSpecialization;
    }

    public function saveCandidateStaffSpecialization(Candidate $candidate, StaffSpecialization $staffSpecialization)
    {
        $candidate->staff_specialization_id = $staffSpecialization->id;
        $candidate->save();
    }

    public function saveExpertStaffSpecialization(Expert $expert, StaffSpecialization $staffSpecialization)
    {
        $expert->staff_specialization_id = $staffSpecialization->id;
        $expert->save();
    }
}
