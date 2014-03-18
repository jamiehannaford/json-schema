<?php

namespace JsonSchema\Exception;

class InvalidTypeException extends \InvalidArgumentException
{
    public static function factory($name, $value, $requiredType)
    {
        $type = gettype($value);

        $message = sprintf(
            "\"%s\" must be %s %s, you provided %s %s",
            $name,
            self::formArticle($requiredType), $requiredType,
            self::formArticle($type), $type
        );

        return new self($message);
    }

    private static function formArticle($noun)
    {
        return in_array($noun[0], ['a','e','i','o','u']) ? 'an' : 'a';
    }
}