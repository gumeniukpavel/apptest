<?php

namespace App\Db\Entity;

use App\Service\StorageService;

/**
 * MediaFile
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property string $type
 * @property string $path
 * @property string $cropped_path
 * @property string $mime_type
 * @property integer $size
 *
 * @property string $url
 *
 * @property User $user
 */
class MediaFile extends ExtendedBaseEntity
{
    protected $table = 'media_files';

    const TYPE_AUDIO = 'AUDIO';
    const TYPE_VIDEO = 'VIDEO';
    const TYPE_IMAGE = 'IMAGE';

    protected $visible = [
        'id',
        'name',
        'type',
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

    public function getCroppedUrlAttribute(): ?string
    {
        if ($this->type == MediaFile::TYPE_IMAGE)
        {
            if ($this->user->bucket_id)
            {
                $bucketId = $this->user->bucket_id;
            }
            else
            {
                $bucketId = config('filesystems.disks.s3.bucket');
            }
            return "https://" . $bucketId . ".s3.eu-west-1.amazonaws.com/" . $this->cropped_path;
        }
        else
        {
            return null;
        }
    }
}
