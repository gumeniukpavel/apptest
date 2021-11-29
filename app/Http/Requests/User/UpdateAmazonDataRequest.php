<?php

namespace App\Http\Requests\User;

use App\Db\Entity\User;
use App\Db\Entity\UserProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

/**
 * UpdateAmazonDataRequest
 *
 * @property string $bucketId
 * @property string $secretToken
 *
 */
class UpdateAmazonDataRequest extends FormRequest
{
     /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'bucketId' => 'required|string|max:255',
            'secretToken' => 'required|string|max:255',
        ];
    }

    public function updateEntity(User $user): User
    {
        $user->bucket_id = $this->bucketId;
        $user->secret_token = $this->secretToken;
        return $user;
    }
}
