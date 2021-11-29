<?php

namespace App\Http\Requests\Project;

use App\Constant\OrderType;
use App\Db\Entity\Level;
use App\Db\Entity\ProjectStatus;
use App\Db\Entity\Tag;
use App\Http\Requests\ApiFormRequest;
use App\Rules\IsEnumValueRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * GetListRequest
 *
 * @property int $page
 * @property string $searchString
 * @property int | null $createDateStart
 * @property int | null $createDateEnd
 * @property int | null $finishDateStart
 * @property int | null $finishDateEnd
 * @property int | null $statusId
 * @property int | null $levelId
 * @property string | null $orderType
 * @property array | null $tags
 *
 */
class GetListRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer',
            'createDateStart' => 'nullable|integer',
            'createDateEnd' => 'nullable|integer',
            'finishDateStart' => 'nullable|integer',
            'finishDateEnd' => 'nullable|integer',
            'levelId' => [
                'nullable',
                Rule::exists(Level::class, 'id')
            ],
            'statusId' => [
                    'nullable',
                    Rule::exists(ProjectStatus::class, 'id')
                ],
            'searchString' => 'nullable|string|max:255',
            'orderType' => [
                'nullable',
                new IsEnumValueRule(OrderType::class)
            ],
            'tags' => [
                'nullable',
                'array'
            ],
            'tags.*' => [
                Rule::exists(Tag::class, 'id')
            ]
        ];
    }

    public function getCreateDateStart()
    {
        return $this->createDateStart ? Carbon::createFromTimestamp($this->createDateStart)->hours(0)->minutes(0) : null;
    }

    public function getCreateDateEnd()
    {
        return $this->createDateEnd ? Carbon::createFromTimestamp($this->createDateEnd)->hours(24)->minutes(0) : null;
    }
}
