<?php

namespace App\Db\Service;

use App\Db\Entity\Category;
use App\Db\Entity\Level;
use App\Db\Entity\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CategoryDao
{
    public function getCategoriesStatisticQuery(User $user): \Illuminate\Database\Query\Builder
    {
        return DB::table(Category::tableName() . ' AS c')
            ->select([
                'c.name',
                DB::raw('ifnull(
                        (
                            select count(tr.project_candidate_id) as counter
                            from tests as t
                            join test_results as tr on t.id = tr.test_id
                            where t.category_id = c.id and t.level_id = '.Level::Junior.' and t.user_id = '.$user->id.'
                            group by c.id
                        ),
                        0
                    ) as all_junior_participants'),
                DB::raw('ifnull(
                        (
                            select count(tr.project_candidate_id) as counter
                            from tests as t
                            join test_results as tr on t.id = tr.test_id
                            where t.category_id = c.id and t.level_id = '.Level::Junior.' and t.user_id = '.$user->id.' and tr.is_passed
                            group by c.id
                        ),
                        0
                    ) as pass_junior_participants'),
                DB::raw('ifnull(
                        (
                            select count(tr.project_candidate_id) as counter
                            from tests as t
                            join test_results as tr on t.id = tr.test_id
                            where t.category_id = c.id and t.level_id = '.Level::Middle.' and t.user_id = '.$user->id.'
                            group by c.id
                        ),
                        0
                    ) as all_middle_participants'),
                DB::raw('ifnull(
                        (
                            select count(tr.project_candidate_id) as counter
                            from tests as t
                            join test_results as tr on t.id = tr.test_id
                            where t.category_id = c.id and t.level_id = '.Level::Middle.' and t.user_id = '.$user->id.' and tr.is_passed
                            group by c.id
                        ),
                        0
                    ) as pass_middle_participants'),
                DB::raw('ifnull(
                        (
                            select count(tr.project_candidate_id) as counter
                            from tests as t
                            join test_results as tr on t.id = tr.test_id
                            where t.category_id = c.id and t.level_id = '.Level::Senior.' and t.user_id = '.$user->id.'
                            group by c.id
                        ),
                        0
                    ) as all_senior_participants'),
                DB::raw('ifnull(
                        (
                            select count(tr.project_candidate_id) as counter
                            from tests as t
                            join test_results as tr on t.id = tr.test_id
                            where t.category_id = c.id and t.level_id = '.Level::Senior.' and t.user_id = '.$user->id.' and tr.is_passed
                            group by c.id
                        ),
                        0
                    ) as pass_senior_participants'),
            ]);
    }
}
