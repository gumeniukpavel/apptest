<?php

namespace App\Http\Requests\Question;

use App\Db\Entity\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * UploadAudioRequest
 *
 * @property UploadedFile $audio
 */
class UploadAudioRequest extends FormRequest
{
    public function rules()
    {
        return [
            'audio' => 'required |mimes:mp3, audio/mpeg | max:30000'
        ];
    }
}
