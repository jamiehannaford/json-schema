<?php

namespace JsonSchema\Validator\Constraint;

class StringConstraint extends AbstractConstraint
{
    private $regexValidation = false;

    public function validate()
    {
        if (!$this->validateType()) {
            return false;
        }

        if (true === $this->regexValidation) {
            if (false === @preg_match($this->value, null)) {
                return false;
            }
        }

        return true;
    }

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
