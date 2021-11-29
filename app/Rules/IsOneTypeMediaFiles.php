<?php

namespace App\Rules;

use App\Db\Entity\MediaFile;
use Illuminate\Contracts\Validation\Rule;

class IsOneTypeMediaFiles implements Rule
{
    private $message;

    public function __construct(string $message = null)
    {
        $this->message = $message;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $values
     * @return bool
     */
    public function passes($attribute, $values)
    {
        $isOneType = true;
        $type = null;

        foreach ($values as $key => $value)
        {
            if (!is_int($value))
            {
                $this->message = trans('questions.mediaFileValidationError');
                return false;
            }

            $mediaFile = MediaFile::byId($value);
            if (!$mediaFile)
            {
                $this->message = trans('questions.mediaFileValidationError');
                return false;
            }

            if ($key == 0)
            {
                $type = $mediaFile->type;
            }
            else
            {
                if ($mediaFile->type != $type)
                {
                    $isOneType = false;
                }
            }
        }

        return $isOneType;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->message)
        {
            return $this->message;
        }

        return 'The :attribute must be greater than select field.';
    }
}
