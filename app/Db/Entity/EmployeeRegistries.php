<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * EmployeeRegistries
 *
 * @property int $id
 * @property int $user_id
 * @property int $candidate_id
 * @property string $event
 * @property string $event_details
 * @property int $date
 * @property string $order_number
 * @property string $notes
 *
 * @property User $user
 * @property Candidate $candidate
 * @property EmployeeRegistriesFile[] $documents
 */
class EmployeeRegistries extends BaseEntity
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'candidate_id',
        'event',
        'event_details',
        'date',
        'order_number',
        'notes'
    ];

    protected $visible = [
        'id',
        'candidate_id',
        'event',
        'event_details',
        'date',
        'order_number',
        'notes',

        'documents'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeRegistriesFile::class);
    }
}
