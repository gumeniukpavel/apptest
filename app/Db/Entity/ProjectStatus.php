<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ProjectStatus
 *
 * @property int $id
 * @property string $status
 *
 * @property Project[] | Collection $projects
 */
class ProjectStatus extends BaseEntity
{
    use HasFactory;

    const Open = 1;
    const Closed = 2;

    protected $visible = [
        'id',
        'status',
        'projects'
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_status');
    }

    /** @return ProjectStatus | Model */
    public static function getStatus(int $statusId) : Model
    {
        return self::query()->where('id', $statusId)->first();
    }
}
