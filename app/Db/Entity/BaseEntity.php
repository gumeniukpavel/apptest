<?php

namespace App\Db\Entity;

use App\Db\Entity\Extensions\PrepareToArrayData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * BaseEntity
 *
 * @property Carbon $updated_at
 * @property Carbon $created_at
 */
class BaseEntity extends Model
{
    use PrepareToArrayData, HasFactory;

    public static function tableName() : string
    {
        return with(new static)->getTable();
    }

    /**
     * @return self | Model | null
     */
    public static function byId(int $id) : ?self
    {
        return self::query()->where('id', $id)->first();
    }
}
