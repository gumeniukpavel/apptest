<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use TCG\Voyager\Facades\Voyager;

/**
 * UserRole
 *
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property Carbon $updated_at
 * @property Carbon $created_at
 */
class Role extends BaseEntity
{
    use HasFactory;

    const ROLE_ADMIN = 1;
    const ROLE_CUSTOMER = 2;
    const ROLE_CANDIDATE = 3;
    const ROLE_USER = 4;
    const ROLE_BOOKKEEPER = 5;
    const ROLE_ADVERTISER = 6;

    const ROLE_NAME_ADMIN = 'admin';
    const ROLE_NAME_CUSTOMER = 'customer';
    const ROLE_NAME_CANDIDATE = 'candidate';
    const ROLE_NAME_USER = 'user';
    const ROLE_NAME_BOOKKEEPER = 'bookkeeper';
    const ROLE_NAME_ADVERTISER = 'advertiser';

    protected $visible = [
        'id',
        'name',
        'display_name'
    ];

    protected $guarded = [];

    public function users()
    {
        $userModel = Voyager::modelClass('User');

        return $this->belongsToMany($userModel, 'user_roles')
            ->select(app($userModel)->getTable().'.*')
            ->union($this->hasMany($userModel))->getQuery();
    }

    public function permissions()
    {
        return $this->belongsToMany(Voyager::modelClass('Permission'));
    }
}
