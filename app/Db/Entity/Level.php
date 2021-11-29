<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Level
 *
 * @property int $id
 * @property string $name
 *
 * @property Test[] | Collection $tests
 * @property Candidate[] | Collection $candidates
 */
class Level extends ExtendedBaseEntity
{
    use HasFactory;

    const Junior = 1;
    const Middle = 2;
    const Senior = 3;

    public $timestamps = false;

    protected $fillable = ['name'];

    protected $visible = [
        'id',
        'name',
        'tests',
        'candidates'
    ];

    public function tests()
    {
        return $this->hasMany(Test::class);
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
