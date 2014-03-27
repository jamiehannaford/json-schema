<?php

namespace JsonSchema\Validator\Constraint;

class ObjectConstraint extends AbstractConstraint
{
    private $schemaValidation = false;
    private $nestedSchemaValidation = false;
    private $patternPropertiesValidation = false;
    private $dependencyValidation = false;

    public function validate()
    {
        if (!$this->validateType()) {
            return false;
        }

        $constraintFactory = new ConstraintFactory();


        if (true === $this->patternPropertiesValidation) {
            foreach ($this->value as $key => $value) {
                $constraint  = $constraintFactory->create('StringConstraint', $key, $this->eventDispatcher);
                $constraint->setRegexValidation(true);
                if (!$constraint->validate()) {
                    return false;
                }
            }
        }

        return true;
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

    public function setDependencyValidation($choice)
    {
        $this->dependencyValidation = (bool) $choice;
    }

    public function getDependencyValidation()
    {
        return $this->dependencyValidation;
    }
}
