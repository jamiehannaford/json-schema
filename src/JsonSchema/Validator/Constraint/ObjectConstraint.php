<?php

namespace JsonSchema\Validator\Constraint;

use JsonSchema\Schema\RootSchema;

class ObjectConstraint extends AbstractConstraint
{
    private $schemaValidation = false;
    private $nestedSchemaValidation = false;
    private $patternPropertiesValidation = false;
    private $dependenciesSchemaValidation = false;
    private $dependenciesInstanceValidation;
    private $maxProperties;
    private $minProperties = 0;
    private $requiredElementNames;
    private $strictAdditionalProperties;
    private $regexArray;
    private $schemaDependencies;
    private $allowedPropertyNames;

    public function validateConstraint()
    {
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

        if (true === $this->patternPropertiesValidation) {
            foreach ($this->value as $key => $value) {
                // Check that keys are valid regex strings
                $constraint  = $this->constraintFactory->create('StringConstraint', $key, $this->eventDispatcher);
                $constraint->setRegexValidation(true);
                if (!$constraint->validate()) {
                    return false;
                }

                // Check that vals are valid schemas
                $constraint = $this->constraintFactory->create('ObjectConstraint', $value, $this->eventDispatcher);
                $constraint->setSchemaValidation(true);
                if (!$constraint->validate()) {
                    return false;
                }
            }
        }

        // Validate schema `dependencies` value
        if (true === $this->dependenciesSchemaValidation) {
            foreach ($this->value as $value) {
                $arrayConstraint = $this->constraintFactory->create('ArrayConstraint', $value, $this->eventDispatcher);
                $arrayConstraint->setInternalType('string');
                $arrayConstraint->setMinimumCount(1);

                $objectConstraint = $this->constraintFactory->create('ObjectConstraint', $value, $this->eventDispatcher);
                $objectConstraint->setSchemaValidation(true);

                if (!$arrayConstraint->validate() && !$objectConstraint->validate()) {
                    return false;
                }
            }
        }

        // Validate instance `dependencies` value
        if (true === $this->dependenciesInstanceValidation) {
            // Schema dependencies
            if (!empty($this->schemaDependencies)) {
                $schemas = get_object_vars($this->schemaDependencies);
                foreach ($this->value as $key => $value) {
                    if (isset($schemas[$key])) {
                        // First create the schema object
                        $schema = $this->createRootSchema($schemas[$key]);

                        // Now validate the instance against this schema
                        if (true !== $schema->validateInstanceData($value)) {
                            return false;
                        }
                    }
                }
            }

            // Property dependencies
            if (is_array($this->allowedPropertyNames)) {
                $properties = array_keys(get_object_vars($this->value));
                if (count(array_diff($this->allowedPropertyNames, $properties))) {
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

    public function setDependenciesSchemaValidation($choice)
    {
        $this->dependenciesSchemaValidation = (bool) $choice;
    }

    public function getDependenciesSchemaValidation()
    {
        return $this->dependenciesSchemaValidation;
    }

    public function setDependenciesInstanceValidation($choice)
    {
        $this->dependenciesInstanceValidation = (bool) $choice;
    }

    public function getDependenciesInstanceValidation()
    {
        return $this->dependenciesInstanceValidation;
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

    public function setSchemaDependencies($schemaDependencies)
    {
        $this->schemaDependencies = $schemaDependencies;
    }

    public function getSchemaDependencies()
    {
        return $this->schemaDependencies;
    }

    public function setPropertyDependencies($argument1)
    {
        // TODO: write logic here
    }

    public function getPropertyDependencies()
    {
        // TODO: write logic here
    }
}
