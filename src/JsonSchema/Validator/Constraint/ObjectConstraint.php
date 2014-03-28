<?php

namespace JsonSchema\Validator\Constraint;

class ObjectConstraint extends AbstractConstraint
{
    private $schemaValidation = false;
    private $nestedSchemaValidation = false;
    private $patternPropertiesValidation = false;
    private $dependenciesValidation = false;

    public function validate()
    {
        if (!$this->validateType()) {
            return false;
        }

        if (true === $this->schemaValidation) {
            if (true !== $this->validateSchema($this->value)) {
                return false;
            }
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

    public function setDependenciesValidation($choice)
    {
        $this->dependenciesValidation = (bool) $choice;
    }

    public function getDependenciesValidation()
    {
        return $this->dependenciesValidation;
    }
}
