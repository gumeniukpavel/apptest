<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TariffPeriod
 *
 * @property int $id
 * @property integer $tariff_period_id
 * @property integer $tariff_id
 * @property float $price
 *
 * @property Tariff $tariff
 * @property TariffPeriod $tariffPeriod
 */
class TariffPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'price'
    ];

    protected $visible = [
        'id',
        'price',
        'tariffPeriod'
    ];

    public $timestamps = false;

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function tariffPeriod()
    {
        return $this->belongsTo(TariffPeriod::class);
    }
}
