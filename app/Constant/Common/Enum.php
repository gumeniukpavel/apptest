<?php

namespace App\Constant\Common;


use Illuminate\Support\Facades\Log;

class Enum
{
    private static $values = [];
    private static $titles = [];
    private static $valueMap = [];

    private $value;
    private $title;

    public function __construct($value, $title = '')
    {
        $this->value = $value;
        $this->title = $title;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function __toString()
    {
        return !empty($this->title) ? $this->title : $this->value;
    }

    public function toString()
    {
        return !empty($this->title) ? $this->title : $this->value;
    }

    public function isEquals($value)
    {
        return $this->value == "$value";
    }

    /**
     * @return Enum[]
     * @throws \Exception
     */
    public static function getValues()
    {
        $className = get_called_class();
        if (!array_key_exists($className, self::$values)) {
            throw new \Exception(sprintf("Enum is not initialized, enum=%s", $className));
        }
        return self::$values[$className];
    }

    /* @return Enum|object */
    public static function getEnumObject($value)
    {
        if (empty($value)) {
            return null;
        }
        $className = get_called_class();
        return isset(self::$valueMap[$className][$value]) ? self::$valueMap[$className][$value] : null;
    }

    public static function init()
    {
        $className = get_called_class();
        $class = new \ReflectionClass($className);

        if (array_key_exists($className, self::$values)) {
            $error = sprintf("Enum has been already initialized, enum=%s", $className);
            Log::error($error);
            throw new \Exception($error);
        }
        self::$values[$className] = [];
        self::$titles[$className] = [];
        self::$valueMap[$className] = [];


        /** @var Enum[] $enumFields */
        $enumFields = array_filter($class->getStaticProperties(), function ($property) {
            return $property instanceof Enum;
        });
        if (count($enumFields) == 0) {
            throw new \Exception(sprintf("Enum has not values, enum=%s", $className));
        }

        foreach ($enumFields as $property) {
            if (array_key_exists($property->getValue(), self::$valueMap[$className])) {
                throw new \Exception(sprintf("Duplicate enum value %s from enum %s", $property->getValue(), $className));
            }

            self::$values[$className][] = $property;
            self::$titles[$className][] = "$property";
            self::$valueMap[$className][$property->getValue()] = $property;
        }
    }

    public static function toArray() : array
    {
        $className = get_called_class();
        $result = [];
        /* @var $properties Enum[] */
        $properties = self::$valueMap[$className];
        foreach ($properties as $property)
        {
            $result[$property->getValue()] = $property->toString();
        }
        return $result;
    }
}
