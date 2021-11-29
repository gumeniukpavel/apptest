<?php

namespace App\Http\Requests\Question;

use App\Db\Entity\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * UploadAudioRequest
 *
 * @property UploadedFile $video
 */
class UploadVideoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'video' => 'required|mimes:mp4, video/mp4|max:150000'
        ];
    }
}
