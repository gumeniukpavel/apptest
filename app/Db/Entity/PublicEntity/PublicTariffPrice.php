<?php

namespace App\Db\Entity\PublicEntity;

use App\Db\Entity\TariffPrice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PublicTariffPrice
 *
 * @property int $id
 * @property integer $tariff_period_id
 * @property integer $tariff_id
 * @property float $price
 *
 * @property PublicTariff $tariff
 * @property PublicTariffPeriod $tariffPeriod
 */
class PublicTariffPrice extends TariffPrice
{
    protected $table = 'tariff_prices';

    protected $visible = [
        'price',
        'tariffPeriod'
    ];

    public function tariff()
    {
        return $this->belongsTo(PublicTariff::class, 'tariff_id');
    }

    public function tariffPeriod()
    {
        return $this->belongsTo(PublicTariffPeriod::class, 'tariff_period_id');
    }
}
