<?php

namespace App\Db\Service;

use App\Constant\OrderType;
use App\Db\Entity\Candidate;
use App\Db\Entity\EmployeeRegistries;
use App\Db\Entity\User;
use App\Http\Requests\EmployeeRegistries\AddRequest;
use App\Http\Requests\EmployeeRegistries\UpdateRequest;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRegistriesDao
{
    public function list(
        User $user,
        ?string $searchString
    ): Builder
    {
        $builder = EmployeeRegistries::query()
            ->where('user_id', $user->id)
            ->when(!empty($searchString), function (Builder $builder) use ($searchString)
            {
                $builder->whereIn('employee_registries.id', function ($query) use ($searchString)
                {
                    $query->select('employee_registries.id')
                        ->from('employee_registries')
                        ->whereRaw('UPPER(`event`) LIKE ?', [mb_strtoupper('%' . $searchString . '%', 'UTF-8')]);
                });
            });
        $builder->orderByDesc('date');
        return $builder;
    }

    public function searchQuery(
        User $user,
        Candidate $candidate,
        ?string $searchString,
        ?string $orderType,
        ?int $fromDate,
        ?int $toDate
    ): Builder
    {
        $builder = EmployeeRegistries::query()
            ->with(['documents'])
            ->where('user_id', $user->id)
            ->where('candidate_id', $candidate->id)
            ->when(!empty($searchString), function (Builder $builder) use ($searchString)
            {
                $builder->whereIn('employee_registries.id', function ($query) use ($searchString)
                {
                    $query->select('employee_registries.id')
                        ->from('employee_registries')
                        ->whereRaw('UPPER(`event`) LIKE ?', [mb_strtoupper('%' . $searchString . '%', 'UTF-8')]);
                });
            })
            ->when(!empty($fromDate), function (Builder $builder) use ($fromDate)
            {
                $builder->where('date', '>=', $fromDate);
            })
            ->when(!empty($toDate), function (Builder $builder) use ($toDate)
            {
                $builder->where('date', '<=', $toDate);
            });

        if ($orderType)
        {
            switch ($orderType)
            {
                case OrderType::$CreatedAtAsc->getValue():
                    $builder->orderBy('date', 'asc');
                    break;

                case OrderType::$CreatedAtDesc->getValue():
                    $builder->orderBy('date', 'desc');
                    break;

                case OrderType::$NameAsc->getValue():
                    $builder->orderBy('event', 'asc');
                    break;

                case OrderType::$NameDesc->getValue():
                    $builder->orderBy('event', 'desc');
                    break;
            }
        }
        else
        {
            $builder->orderByDesc('date');
        }
        return $builder;
    }

    /** @returns EmployeeRegistries | null */
    public function firstWithData(int $id): ?EmployeeRegistries
    {
        /** @var EmployeeRegistries $employeeRegistries */
        $employeeRegistries = EmployeeRegistries::query()
            ->with(['documents'])
            ->where('id', $id)
            ->first();

        return $employeeRegistries;
    }

    public function create(AddRequest $request, User $user): EmployeeRegistries
    {
        $employeeRegistries = new EmployeeRegistries();
        $employeeRegistries->user_id = $user->id;
        $employeeRegistries->candidate_id = $request->employeeId;
        $employeeRegistries->event = $request->event;
        $employeeRegistries->event_details = $request->eventDetails;
        $employeeRegistries->date = $request->date;
        $employeeRegistries->order_number = $request->orderNumber;
        $employeeRegistries->notes = $request->notes;
        $employeeRegistries->save();

        return $employeeRegistries;
    }

    public function update(UpdateRequest $request, EmployeeRegistries $employeeRegistries): EmployeeRegistries
    {
        $employeeRegistries->event = $request->event;
        $employeeRegistries->event_details = $request->eventDetails;
        $employeeRegistries->date = $request->date;
        $employeeRegistries->order_number = $request->orderNumber;
        $employeeRegistries->notes = $request->notes;
        $employeeRegistries->save();

        return $employeeRegistries;
    }
}
