<?php

namespace App\Service;

class ImageModel
{
    public ?string $path;
    public ?string $pathCropped;

    public function __construct(array $result)
    {
        $this->path = $result[ 'path' ];
        $this->pathCropped = $result[ 'croppedPath' ];
    }
}
