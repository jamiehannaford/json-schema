<?php

namespace JsonSchema\Validator\Constraint;

class StringConstraint extends AbstractConstraint
{
    private $regexValidation = false;
    private $primitiveTypeValidation = false;

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

        if (true === $this->primitiveTypeValidation) {
            if (!$this->validatePrimitiveType($this->value)) {
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

    public function getPrimitiveTypeValidation()
    {
        return $this->primitiveTypeValidation;
    }

    public function setPrimitiveTypeValidation($choice)
    {
        $this->primitiveTypeValidation = (bool) $choice;
    }
}
