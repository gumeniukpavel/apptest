<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * UserTariffCategory
 *
 * @property int $id
 * @property int $user_id
 * @property int $tariff_id
 * @property int $category_id
 *
 * @property User $user
 * @property Tariff $tariff
 * @property Category $category
 */
class UserTariffCategory extends Model
{
    use HasFactory;

    protected $visible = [
        'id',
        'user_id',
        'tariff_id',
        'category_id',

        'user',
        'tariff',
        'category',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
