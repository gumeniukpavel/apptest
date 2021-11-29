<?php

namespace App\Db\Entity;

use App\Service\StorageService;

/**
 * CandidateFile
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $candidate_id
 * @property string $type
 * @property string $name
 * @property string $path
 * @property string $cropped_path
 * @property string $mime_type
 * @property integer $size
 *
 * @property string $url
 *
 * @property User $user
 * @property Candidate $candidate
 */
class CandidateFile extends ExtendedBaseEntity
{
    const TYPE_IMAGE = 'IMAGE';
    const TYPE_DOCUMENT = 'DOCUMENT';

    protected $table = 'candidate_files';

    protected $visible = [
        'id',
        'type',
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

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
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
