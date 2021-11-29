<?php

namespace App\Db\Entity;

/**
 * ExpertTag
 *
 * @property int $id
 * @property int $expert_id
 * @property int $tag_id
 *
 * @property Expert[] $experts
 * @property Tag $tag
 */
class ExpertTag extends BaseEntity
{
    public $timestamps = false;

    protected $fillable = ['expert_id', 'tag_id'];

    protected $visible = [
        'id',
        'expert_id',
        'tag_id'
    ];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
