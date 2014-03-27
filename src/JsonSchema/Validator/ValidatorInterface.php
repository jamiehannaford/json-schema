<?php

namespace JsonSchema\Validator;

use JsonSchema\Validator\ErrorHandler\ErrorHandlerInterface;

interface ValidatorInterface
{
    public function setData($data);

    public function setErrorHandler(ErrorHandlerInterface $handler);

    //public function validate();
}