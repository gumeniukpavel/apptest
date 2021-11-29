<?php

namespace App\Rules;

use App\Constant\Common\Enum;
use Exception;
use Illuminate\Contracts\Validation\Rule;

class IsEnumValueRule implements Rule
{
    /** @var Enum */
    private $enumClass;

    public function __construct($class)
    {
        $reflector = new \ReflectionClass($class);
        if (!$reflector->isSubclassOf(Enum::class))
        {
            throw new Exception('Enum class is should be instance of ' . Enum::class);
        }

        $this->enumClass = $class;
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
        return $this->enumClass::getEnumObject($value) != null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be enum value.';
    }
}
