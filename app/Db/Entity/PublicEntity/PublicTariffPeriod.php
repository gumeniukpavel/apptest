<?php

namespace App\Db\Entity\PublicEntity;

use App\Db\Entity\TariffPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * PublicTariffPeriod
 *
 * @property int $id
 * @property string $name
 * @property integer $months
 */
class PublicTariffPeriod extends TariffPeriod
{
    protected $table = 'tariff_periods';

    protected $visible = [
        'name',
        'months',
    ];
}
