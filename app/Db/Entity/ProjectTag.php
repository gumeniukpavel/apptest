<?php

namespace App\Db\Entity;

/**
 * ProjectTag
 *
 * @property int $id
 * @property int $project_id
 * @property int $tag_id
 *
 * @property Project[] $projects
 * @property Tag $tags
 */
class ProjectTag extends BaseEntity
{
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'tag_id'
    ];

    protected $visible = [
        'id',
        'project_id',
        'tag_id'
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
