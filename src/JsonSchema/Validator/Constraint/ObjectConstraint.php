<?php

namespace JsonSchema\Validator\Constraint;

class ObjectConstraint extends AbstractConstraint
{
    const TYPE = 'object';

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
            return $this->logFailure('Object is not a valid schema');
        }

        if (true === $this->nestedSchemaValidation) {
            foreach ($this->value as $value) {
                if (true !== $this->validateSchema($value)) {
                    return $this->logFailure('Object contains invalid nested schemas');
                }
            }
        }

        if (true === $this->patternPropertiesValidation) {
            foreach ($this->value as $key => $value) {
                // Check that keys are valid regex strings
                if (!$this->validateRegex($key)) {
                    return $this->logFailure('Object contains keys which are invalid regular expressions', null, $key);
                }

                // Check that vals are valid schemas
                if (!$this->validateSchema($value)) {
                    return $this->logFailure('Object contains values which are invalid schemas', null, $value);
                }
            }
        }

        // Validate schema `dependencies` value
        if (true === $this->dependenciesSchemaValidation) {
            foreach ($this->value as $value) {
                if (is_array($value)) {
                    $arrayConstraint = $this->constraintFactory->create('ArrayConstraint', $value, $this->eventDispatcher);
                    $arrayConstraint->setInternalType('string');
                    $arrayConstraint->setMinimumCount(1);
                    if (!$arrayConstraint->validate()) {
                        return false;
                    }
                } elseif (is_object($value) && !$this->validateSchema($value)) {
                    return $this->logFailure('Objects provided as values must be valid schemas');
                } else {
                    return $this->logFailure('Object values need to be either objects or array', null, $value);
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
                        $schemaData = $schemas[$key];
                        $schema = $this->createRootSchema($schemaData);
                        if (true !== $schema->validateInstanceData($value)) {
                            return $this->logFailure('The object values provided fail to validate against the given schema', $schemaData);
                        }
                    }
                }
            }

            // Property dependencies
            if (is_array($this->allowedPropertyNames)) {
                $properties = array_keys(get_object_vars($this->value));
                if (count(array_diff($this->allowedPropertyNames, $properties))) {
                    return $this->logFailure('Object does not contain property dependencies', $this->allowedPropertyNames);
                }
            }
        }

        if (is_int($this->maxProperties) && $this->getCount() > $this->maxProperties) {
            return $this->logFailure('Object has more properties than allowed', $this->maxProperties);
        }

        if (is_int($this->minProperties) && $this->getCount() < $this->minProperties) {
            return $this->logFailure('Object does not have enough properties', $this->minProperties);
        }

        if (is_array($this->requiredElementNames)) {
            $keys = array_keys(get_object_vars($this->value));
            if (count(array_diff($this->requiredElementNames, $keys))) {
                return $this->logFailure('Object does not contain required properties', $this->requiredElementNames);
            }
        }

        if (true === $this->strictAdditionalProperties && true !== $this->validateStrictProperties()) {
            return $this->logFailure('Some object properties either do not '
                . 'match the names defined in `properties` or match the regular '
                . 'expressions defined in `patterProperties`'
            );
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
}
