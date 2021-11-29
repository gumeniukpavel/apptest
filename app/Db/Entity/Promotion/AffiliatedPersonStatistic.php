<?php

namespace App\Db\Entity\Promotion;

use App\Db\Entity\BaseEntity;
use App\Db\Entity\Tariff;
use App\Db\Entity\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class AffiliatedPersonStatistic
 * @property int $id
 * @property int $affiliated_person_id
 * @property int $user_id
 * @property int $tariff_id
 *
 * @property User $user
 * @property Tariff $tariff
 * @property AffiliatedPerson $affiliatedPerson
 */
class AffiliatedPersonStatistic extends BaseEntity
{
    use HasFactory;

    protected $table = 'affiliated_person_statistics';

    const UPDATED_AT = null;

    protected $fillable = [
        'affiliated_person_id',
        'user_id',
        'tariff_id',
    ];

    protected $visible = [
        'user',
        'tariff',
        'affiliatedPerson'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tariff(): HasOne
    {
        return $this->hasOne(Tariff::class);
    }

    public function affiliatedPerson(): HasOne
    {
        return $this->hasOne(AffiliatedPerson::class);
    }
}
