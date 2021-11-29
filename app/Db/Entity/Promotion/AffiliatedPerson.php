<?php


namespace App\Db\Entity\Promotion;


use App\Db\Entity\BaseEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class AffiliatedPerson
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $city
 * @property string $promo_code
 *
 */
class AffiliatedPerson extends BaseEntity
{
    use HasFactory;

    protected $table = 'affiliated_persons';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'city'
    ];

    protected $visible = [
        'name',
        'email',
        'phone',
        'city',
        'promo_code'
    ];

    public function affiliatedPersonStatistics(): HasMany
    {
        return $this->hasMany(AffiliatedPersonStatistic::class);
    }
}
