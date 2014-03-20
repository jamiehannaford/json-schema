<?php

namespace JsonSchema\Enum;

abstract class BaseEnum
{
    private static $constants;

    private static function getConstants()
    {
        if (self::$constants === null) {
            $reflect = new \ReflectionClass(get_called_class());
            self::$constants = $reflect->getConstants();
        }

        return self::$constants;
    }

    public static function isValidName($name, $strict = false)
    {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value)
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict = true);
    }
}