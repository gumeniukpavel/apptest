<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * UserTariffTest
 *
 * @property int $id
 * @property int $user_id
 * @property int $tariff_id
 * @property int $test_id
 *
 * @property User $user
 * @property Tariff $tariff
 * @property Test $test
 */
class UserTariffTest extends Model
{
    use HasFactory;

    protected $visible = [
        'id',
        'user_id',
        'tariff_id',
        'test_id',

        'user',
        'tariff',
        'test',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
