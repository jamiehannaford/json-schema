<?php

namespace JsonSchema\Validator\Constraint;

class StringConstraint extends AbstractConstraint
{
    const TYPE = 'string';

    private $regexValidation = false;
    private $primitiveTypeValidation = false;
    private $maxLength;
    private $minLength = 0;

    public function validateConstraint()
    {
        if (true === $this->regexValidation && true !== $this->validateRegex($this->value)) {
            return $this->logFailure('Value is not a valid regular expression');
        } elseif (is_string($this->regexValidation)
            && !preg_match($this->regexValidation, $this->value)
        ) {
            return $this->logFailure('Value does not satisfy regular expression', $this->regexValidation);
        }

        if (true === $this->primitiveTypeValidation && !$this->validatePrimitiveType($this->value)) {
            return $this->logFailure('Value is not a valid primitive type', $this->jsonPrimitiveType);
        }

        if (is_int($this->maxLength) && strlen($this->value) > $this->maxLength) {
            return $this->logFailure('Value contains more characters than allowed', $this->maxLength);
        }

        if (is_int($this->minLength) && strlen($this->value) < $this->minLength) {
            return $this->logFailure('Value does not contain enough characters', $this->minLength);
        }

        return true;
    }

    public function hasCorrectType()
    {
        return is_string($this->value);
    }

    public function setRegexValidation($choice)
    {
        if (!is_bool($choice) && !$this->validateRegex($choice)) {
            throw new \InvalidArgumentException("You must specify a boolean (to validate regular expression value) or a string pattern");
        }

        $this->regexValidation = $choice;
    }

    public function hasRegexValidation()
    {
        return is_string($this->regexValidation) || $this->regexValidation === true;
    }

    public function getPrimitiveTypeValidation()
    {
        return $this->primitiveTypeValidation;
    }

    public function setPrimitiveTypeValidation($choice)
    {
        $this->primitiveTypeValidation = (bool) $choice;
    }

    public function setMaxLength($maxLength)
    {
        $this->maxLength = (int) $maxLength;
    }

    public function getMaxLength()
    {
        return $this->maxLength;
    }

    public function setMinLength($minLength)
    {
        $this->minLength = (int) $minLength;
    }

    public function getMinLength()
    {
        return $this->minLength;
    }
}
