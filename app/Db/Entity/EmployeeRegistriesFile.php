<?php

namespace App\Db\Entity;

/**
 * EmployeeRegistriesFile
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $employee_registries_id
 * @property string $name
 * @property string $path
 * @property string $cropped_path
 * @property string $mime_type
 * @property integer $size
 *
 * @property string $url
 *
 * @property User $user
 * @property EmployeeRegistries $employeeRegistries
 */
class EmployeeRegistriesFile extends ExtendedBaseEntity
{
    protected $table = 'employee_registries_files';

    protected $visible = [
        'id',
        'name',
        'path',
        'cropped_path',
        'mime_type',
        'url',
    ];

    protected $dateFormat = 'U';

    protected $appends = ['url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employeeRegistries()
    {
        return $this->belongsTo(EmployeeRegistries::class);
    }

    public function getUrlAttribute(): string
    {
        if ($this->user->bucket_id)
        {
            $bucketId = $this->user->bucket_id;
        }
        else
        {
            $bucketId = config('filesystems.disks.s3.bucket');
        }
        return "https://" . $bucketId . ".s3.eu-west-1.amazonaws.com/" . $this->path;
    }
}
