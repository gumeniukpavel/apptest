<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Tariff
 *
 * @property int $id
 * @property string $name
 * @property int $tests_count
 * @property int $candidates_count
 * @property int $categories_count
 * @property int $tariff_tests_count
 * @property boolean $is_recommended
 * @property boolean $is_private
 * @property boolean $is_unlimited_tests
 * @property boolean $is_unlimited_candidates
 * @property boolean $is_unlimited_categories
 * @property boolean $is_unlimited_tariff_tests
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property TariffPrice[] $tariffPrices
 */
class Tariff extends BaseEntity
{
    use HasFactory;

    const EconomyTariff = 1;
    const M1 = 2;
    const M2 = 3;
    const Premium1 = 4;
    const Premium2 = 5;
    const Beta = 6;
    const Corporate = 7;


    protected $fillable = [
        'name'
    ];

    protected $visible = [
        'id',
        'name',
        'tests_count',
        'candidates_count',
        'tariff_tests_count',
        'categories_count',
        'is_unlimited_tests',
        'is_unlimited_candidates',
        'is_unlimited_categories',
        'is_unlimited_tariff_tests',
        'is_recommended',

        'tariffPrices',
    ];

    public function tariffPrices()
    {
        return $this->hasMany(TariffPrice::class);
    }
}
