<?php

namespace JsonSchema\Validator\Constraint;

class ObjectConstraint extends AbstractConstraint
{
    private $schemaValidation = false;
    private $nestedSchemaValidation = false;
    private $nestedRegexValidation = false;
    private $dependencyValidation = false;

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

    public function setNestedRegexValidation($choice)
    {
        $this->nestedRegexValidation = (bool) $choice;
    }

    public function getNestedRegexValidation()
    {
        return $this->nestedRegexValidation;
    }

    public function setDependencyValidation($choice)
    {
        $this->dependencyValidation = (bool) $choice;
    }

    public function getDependencyValidation()
    {
        return $this->dependencyValidation;
    }
}
