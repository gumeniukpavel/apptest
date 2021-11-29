<?php

namespace App\Rules;

use App\Constant\Common\Enum;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class IsYearLessThanDate implements Rule
{
    private int $year;
    private $message;

    public function __construct(int $date, string $message = null)
    {
        $this->year = Carbon::createFromTimestamp($date)->year;
        $this->message = $message;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->year < $value;
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
