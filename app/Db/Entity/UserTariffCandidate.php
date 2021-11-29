<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * UserTariffCandidate
 *
 * @property int $id
 * @property int $user_id
 * @property int $tariff_id
 * @property int $candidate_id
 *
 * @property User $user
 * @property Tariff $tariff
 * @property Candidate $candidate
 */
class UserTariffCandidate extends Model
{
    use HasFactory;

    protected $visible = [
        'id',
        'user_id',
        'tariff_id',
        'candidate_id',

        'user',
        'tariff',
        'candidate',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
