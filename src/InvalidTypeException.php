<?php

class InvalidTypeException extends \InvalidArgumentException
{
    public function __construct($name, $value, $requiredType, $type = null)
    {
        $type = $type ?: gettype($value);

        $message = sprintf(
            "\"%s\" must be a %s, you provided %s %s",
            $name, $requiredType, $this->formArticle($type), $type
        );

        parent::__construct($message);
    }

    private function formArticle($noun)
    {
        return in_array($noun[0], ['a','e','i','o','u']) ? 'an' : 'a';
    }
}