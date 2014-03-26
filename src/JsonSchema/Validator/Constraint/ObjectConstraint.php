<?php

namespace JsonSchema\Validator\Constraint;

class ObjectConstraint extends AbstractConstraint
{
    private $schemaValidation = false;

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
}
