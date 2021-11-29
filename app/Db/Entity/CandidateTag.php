<?php

namespace App\Db\Entity;

/**
 * CandidateTag
 *
 * @property int $id
 * @property int $candidate_id
 * @property int $tag_id
 *
 * @property Candidate[] $candidates
 * @property Tag $tag
 */
class CandidateTag extends BaseEntity
{
    public $timestamps = false;

    protected $fillable = [
        'candidate_id',
        'tag_id'
    ];

    protected $visible = [
        'id',
        'candidate_id',
        'tag_id'
    ];

    public function candidate()
    {
        return $this->belongsToMany(Candidate::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
