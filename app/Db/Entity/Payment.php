<?php

namespace App\Db\Entity;

use App\Db\Entity\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Payment
 *
 * @property int $id
 * @property int $user_id
 * @property int $tariff_id
 * @property int $total
 * @property int $tariff_period_id
 * @property string $status
 * @property string $notes
 * @property string $transaction_number
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User $user
 * @property Tariff $tariff
 * @property TariffPeriod $tariffPeriod
 */
class Payment extends BaseEntity
{
    use HasFactory;

    const PAYMENT_STATUS_PENDING = 'PENDING';
    const PAYMENT_STATUS_COMPLETED = 'COMPLETED';
    const PAYMENT_STATUS_FAILED = 'FAILED';
    const PAYMENT_STATUS_CANCELED = 'CANCELED';

    const PAYMENT_STATUSES = [
        self::PAYMENT_STATUS_PENDING,
        self::PAYMENT_STATUS_COMPLETED,
        self::PAYMENT_STATUS_FAILED,
        self::PAYMENT_STATUS_CANCELED,
    ];

    protected $fillable = [
        'user_id',
        'tariff_id',
        'description',
        'total',
        'tariff_period_id',
        'status',
        'notes',
        'transaction_number'
    ];

    protected $visible = [
        'id',
        'description',
        'total',
        'tariff_period_id',
        'status',
        'notes',
        'transaction_number',

        'user',
        'tariff',
        'tariffPeriod',
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
}
