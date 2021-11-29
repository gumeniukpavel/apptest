<?php

namespace App\Db\Entity;

/**
 * TestTag
 *
 * @property int $id
 * @property int $test_id
 * @property int $tag_id
 *
 * @property Test $test
 * @property Tag $tag
 */
class TestTag extends BaseEntity
{
    public $timestamps = false;

    protected $fillable = [
        'test_id',
        'tag_id'
    ];

    protected $visible = [
        'id',
        'test_id',
        'tag_id'
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
