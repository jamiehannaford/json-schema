<?php

namespace JsonSchema\Validator;

abstract class AbstractValidator implements ValidatorInterface
{
    use HasErrorHandlerTrait;

    protected $data;

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    abstract protected function declareValidationFailure($keyword, $value, $constraintName, $result);
}
