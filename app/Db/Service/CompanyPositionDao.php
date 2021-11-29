<?php

namespace App\Db\Service;

use App\Constant\CandidateType;
use App\Constant\OrderType;
use App\Db\Entity\Candidate;
use App\Db\Entity\CompanyPosition;
use App\Db\Entity\User;
use App\Http\Requests\CompanyPosition\AddRequest;
use App\Http\Requests\CompanyPosition\UpdateRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CompanyPositionDao
{
    public function searchQuery(
        User $user,
        ?string $searchString,
        ?string $orderType
    ): Builder
    {
        $builder = CompanyPosition::query()
            ->where('user_id', $user->id)
            ->withCount(['employees'])
            ->when(!empty($searchString), function (Builder $builder) use ($searchString)
            {
                $builder->whereIn('company_positions.id', function ($query) use ($searchString)
                {
                    $query->select('company_positions.id')
                        ->from('company_positions')
                        ->whereRaw('UPPER(`name`) LIKE ?', [mb_strtoupper('%' . $searchString . '%', 'UTF-8')]);
                });
            });

        if ($orderType)
        {
            switch ($orderType)
            {
                case OrderType::$CreatedAtAsc->getValue():
                    $builder->orderBy('created_at', 'asc');
                    break;

                case OrderType::$CreatedAtDesc->getValue():
                    $builder->orderBy('created_at', 'desc');
                    break;

                case OrderType::$NameAsc->getValue():
                    $builder->orderBy('name', 'asc');
                    break;

                case OrderType::$NameDesc->getValue():
                    $builder->orderBy('name', 'desc');
                    break;
            }
        }
        else
        {
            $builder->orderByDesc('created_at');
        }
        return $builder;
    }

    public function create(User $user, AddRequest $request): CompanyPosition
    {
        $companyPosition = new CompanyPosition();
        $companyPosition->name = $request->name;
        $companyPosition->vacancy_count = $request->vacancyCount;
        $companyPosition->user_id = $user->id;
        $companyPosition->save();

        return $companyPosition;
    }

    public function firstWithData(int $id) : ?Model
    {
        return CompanyPosition::query()
            ->where('id', $id)
            ->withCount(['employees'])
            ->first();
    }

    public function update(UpdateRequest $request, CompanyPosition $companyPosition): CompanyPosition
    {
        $companyPosition->name = $request->name;
        $companyPosition->vacancy_count = $request->vacancyCount;
        $companyPosition->save();

        return $companyPosition;
    }

    public function delete(CompanyPosition $companyPosition)
    {
        /** @var Candidate [] $candidates */
        $candidates = $this->getCandidatesByCompanyPosition($companyPosition);
        foreach ($candidates as $candidate)
        {
            $candidate->company_position_id = null;
            $candidate->save();
        }
        $companyPosition->delete();
    }

    public function getCandidatesByCompanyPosition(CompanyPosition $companyPosition)
    {
        return Candidate::query()
            ->where('company_position_id', $companyPosition->id)
            ->get();
    }

    public function getEmployeeByCompanyPosition(CompanyPosition $companyPosition)
    {
        return Candidate::query()
            ->where([
                'company_position_id' => $companyPosition->id,
                'type' => CandidateType::$Employee->getValue()
            ])
            ->get();
    }
}
