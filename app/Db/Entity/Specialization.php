<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Specialization
 *
 * @property int $id
 * @property string $name
 *
 * @property Candidate[] | Collection $candidates
 */
class Specialization extends ExtendedBaseEntity
{
    use HasFactory;

    const MySQL = 1;
    const CSharp = 2;
    const CPlusPlus = 3;
    const Java = 4;
    const JavaScript = 5;
    const PHP = 6;
    const DotNET = 7;
    const ProjectManagement = 8;
    const QA = 9;
    const Design = 10;
    const Marketing = 11;
    const Html_Css = 12;
    const Sales = 13;
    const Custom = 14;

    public $timestamps = false;

    protected $fillable = ['name'];

    protected $visible = [
        'id',
        'name',
        'candidates'
    ];

    public function candidates()
    {
        return $this->belongsToMany(Candidate::class);
    }
}
