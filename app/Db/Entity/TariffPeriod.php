<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TariffPeriod
 *
 * @property int $id
 * @property string $name
 * @property integer $months
 */
class TariffPeriod extends Model
{
    use HasFactory;

    const TARIFF_PERIOD_ONE_MONTH = 1;
    const TARIFF_PERIOD_THREE_MONTHS = 2;
    const TARIFF_PERIOD_SIX_MONTHS = 3;
    const TARIFF_PERIOD_TWELVE_MONTHS = 4;

    protected $fillable = [
        'name',
        'months'
    ];

    protected $visible = [
        'id',
        'name',
        'months',
    ];

    public $timestamps = false;
}
