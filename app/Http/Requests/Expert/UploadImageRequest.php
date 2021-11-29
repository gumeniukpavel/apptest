<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * UploadImageRequest
 *
 * @property UploadedFile $image
 */
class UploadImageRequest extends FormRequest
{
    public function rules()
    {
        return [
            'image' => [
                'required',
                'max: 5000',
                'mimetypes:image/gif,image/png,image/jpeg'
            ]
        ];
    }
}
