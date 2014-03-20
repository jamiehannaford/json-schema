<?php

namespace JsonSchema\Exception;

class InvalidTypeException extends ValidationException
{
    public static function factory($name, $value, $requiredType)
    {
        $type = gettype($value);

        if (is_array($requiredType)) {
            $rtMessage = '';
            foreach ($requiredType as $type) {
                $rtMessage .= ($rtMessage) ? ' or ': ' ';
                $rtMessage .= sprintf("%s %s", self::formArticle($type), $type);
            }
        } else {
            $rtMessage = sprintf("%s %s", self::formArticle($requiredType), $requiredType);
        }

        $message = sprintf(
            "\"%s\" must be %s, you provided %s %s",
            $name, $rtMessage, self::formArticle($type), $type
        );

        return new self($message);
    }

    private static function formArticle($noun)
    {
        return in_array($noun[0], ['a','e','i','o','u']) ? 'an' : 'a';
    }
}