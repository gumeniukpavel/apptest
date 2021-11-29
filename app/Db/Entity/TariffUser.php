<?php

namespace App\Db\Entity;

use App\Constant\TariffUserStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * TariffUser
 *
 * @property int $id
 * @property int $user_id
 * @property int $tariff_id
 * @property int $tariff_period_id
 * @property boolean $is_active
 * @property integer $ended_at
 * @property Carbon $created_at
 * @property boolean $is_pause
 * @property integer $paused_at
 * @property integer $un_paused_at
 * @property TariffUserStatus $status
 *
 * @property bool $tariffIsOver
 *
 * @property User $user
 * @property Tariff $tariff
 * @property TariffPeriod $tariffPeriod
 */
class TariffUser extends BaseEntity
{
    use HasFactory;

    protected $visible = [
        'id',
        'user_id',
        'tariff_id',
        'tariff_period_id',
        'is_active',
        'ended_at',
        'created_at',
        'is_pause',
        'paused_at',
        'un_paused_at',
        'status',

        'user',
        'tariff',
        'tariffPeriod',
        'tariffIsOver'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function tariffPeriod()
    {
        return $this->belongsTo(TariffPeriod::class);
    }

    public function getTariffIsOverAttribute(): bool
    {
        return Carbon::createFromTimestamp($this->ended_at)->lessThan(Carbon::now());
    }

    public function getStatusAttribute()
    {
        return TariffUserStatus::getEnumObject($this->attributes['status']);
    }

    public function setStatusAttribute(TariffUserStatus $status)
    {
        $this->attributes['status'] = $status->getValue();
    }
}
