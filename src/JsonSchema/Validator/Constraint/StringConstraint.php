<?php

namespace JsonSchema\Validator\Constraint;

class StringConstraint extends AbstractConstraint
{
    private $regexValidation = false;

    public function hasCorrectType()
    {
        return is_string($this->value);
    }

    public function setRegexValidation($choice)
    {
        $this->regexValidation = (bool) $choice;
    }

    public function hasRegexValidation()
    {
        return $this->regexValidation;
    }
}
