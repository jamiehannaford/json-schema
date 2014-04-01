<?php

namespace JsonSchema\Validator\Constraint;

class ObjectConstraint extends AbstractConstraint
{
    private $schemaValidation = false;
    private $nestedSchemaValidation = false;
    private $patternPropertiesValidation = false;
    private $dependenciesValidation = false;
    private $maxProperties;
    private $minProperties = 0;
    private $requiredElementNames;
    private $strictAdditionalProperties;
    private $regexArray;

    private $allowedPropertyNames;

    public function validate()
    {
        if (!$this->validateType()) {
            return false;
        }

        if (true === $this->schemaValidation
            && true !== $this->validateSchema($this->value)
        ) {
            return false;
        }

        if (true === $this->nestedSchemaValidation) {
            foreach ($this->value as $value) {
                if (true !== $this->validateSchema($value)) {
                    return false;
                }
            }
        }

        $constraintFactory = new ConstraintFactory();

        if (true === $this->patternPropertiesValidation) {
            foreach ($this->value as $key => $value) {
                // Check that keys are valid regex strings
                $constraint  = $constraintFactory->create('StringConstraint', $key, $this->eventDispatcher);
                $constraint->setRegexValidation(true);
                if (!$constraint->validate()) {
                    return false;
                }

                // Check that vals are valid schemas
                $constraint = $constraintFactory->create('ObjectConstraint', $value, $this->eventDispatcher);
                $constraint->setSchemaValidation(true);
                if (!$constraint->validate()) {
                    return false;
                }
            }
        }

        if (true === $this->dependenciesValidation) {
            foreach ($this->value as $value) {
                $arrayConstraint = $constraintFactory->create('ArrayConstraint', $value, $this->eventDispatcher);
                $arrayConstraint->setInternalType('string');
                $arrayConstraint->setMinimumCount(1);

                $objectConstraint = $constraintFactory->create('ObjectConstraint', $value, $this->eventDispatcher);
                $objectConstraint->setSchemaValidation(true);

                if (!$arrayConstraint->validate() && !$objectConstraint->validate()) {
                    return false;
                }
            }
        }

        if (is_int($this->maxProperties) && $this->getCount() > $this->maxProperties) {
            return false;
        }

        if (is_int($this->minProperties) && $this->getCount() < $this->minProperties) {
            return false;
        }

        if (is_array($this->requiredElementNames)) {
            $keys = array_keys(get_object_vars($this->value));
            if (count(array_diff($this->requiredElementNames, $keys))) {
                return false;
            }
        }

        if (true === $this->strictAdditionalProperties) {

            if (true !== $this->validateStrictProperties()) {
                return false;
            }
        }

        return true;
    }

    private function getCount()
    {
        return count(get_object_vars($this->value));
    }

    public function hasCorrectType()
    {
        return is_object($this->value);
    }

    public function getSchemaValidation()
    {
        return $this->schemaValidation;
    }

    public function setSchemaValidation($choice)
    {
        $this->schemaValidation = (bool) $choice;
    }

    public function setNestedSchemaValidation($choice)
    {
        $this->nestedSchemaValidation = (bool) $choice;
    }

    public function getNestedSchemaValidation()
    {
        return $this->nestedSchemaValidation;
    }

    public function setPatternPropertiesValidation($choice)
    {
        $this->patternPropertiesValidation = (bool) $choice;
    }

    public function getPatternPropertiesValidation()
    {
        return $this->patternPropertiesValidation;
    }

    public function setDependenciesValidation($choice)
    {
        $this->dependenciesValidation = (bool) $choice;
    }

    public function getDependenciesValidation()
    {
        return $this->dependenciesValidation;
    }

    public function setMaxProperties($count)
    {
        $this->maxProperties = (int) $count;
    }

    public function getMaxProperties()
    {
        return $this->maxProperties;
    }

    public function setMinProperties($count)
    {
        $this->minProperties = (int) $count;
    }

    public function getMinProperties()
    {
        return $this->minProperties;
    }

    public function setRequiredElementNames(array $names)
    {
        $this->requiredElementNames = $names;
    }

    public function getRequiredElementNames()
    {
        return $this->requiredElementNames;
    }

    public function setStrictAdditionalProperties($choice)
    {
        $this->strictAdditionalProperties = (bool) $choice;
    }

    public function getStrictAdditionalProperties()
    {
        return $this->strictAdditionalProperties;
    }

    public function setAllowedPropertyNames($names)
    {
        if (is_object($names)) {
            $names = array_keys(get_object_vars($names));
        } elseif (!is_array($names)) {
            throw new \InvalidArgumentException(
                "You must provide either an array whose values are permissable"
                . " names or an object whose keys are permissable names and"
                . " whose values are object schemas"
            );
        }

        $this->allowedPropertyNames = $names;
    }

    public function getAllowedPropertyNames()
    {
        return $this->allowedPropertyNames;
    }

    public function validateStrictProperties()
    {
        $arrayValue = get_object_vars($this->value);

        foreach ($arrayValue as $key => $value) {
            if (is_array($this->allowedPropertyNames)) {
                if (isset(array_flip($this->allowedPropertyNames)[$key])) {
                    unset($arrayValue[$key]);
                }
            }

            if (is_array($this->regexArray)) {
                foreach ($this->regexArray as $pattern) {
                    if (preg_match($pattern, $key)) {
                        unset($arrayValue[$key]);
                    }
                }
            }
        }

        return count($arrayValue) === 0;
    }

    public function setRegexArray($regexes)
    {
        if (is_object($regexes)) {
            $regexes = array_keys(get_object_vars($regexes));
        } elseif (!is_array($regexes)) {
            throw new \InvalidArgumentException(
                "You must provide either an array whose values are valid"
                . " regular expressions or an object whose keys are valid "
                . " regular expressions and whose values are object schemas"
            );
        }

        foreach ($regexes as $regex) {
            if (true !== $this->validateRegex($regex)) {
                throw new \InvalidArgumentException(sprintf(
                    "%s is not a valid regular expression", $regex
                ));
            }
        }

        $this->regexArray = $regexes;
    }

    public function getRegexArray()
    {
        return $this->regexArray;
    }
}
