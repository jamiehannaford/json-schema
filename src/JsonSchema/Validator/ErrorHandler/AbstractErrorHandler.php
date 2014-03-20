<?php

namespace JsonSchema\Validator\ErrorHandler;

abstract class AbstractErrorHandler implements ErrorHandlerInterface
{
    protected $errors = [];

    public static function getSubscribedEvents()
    {
        return [
            'validation.error' => 'receiveError'
        ];
    }

    public function getErrorCount()
    {
        return count($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}