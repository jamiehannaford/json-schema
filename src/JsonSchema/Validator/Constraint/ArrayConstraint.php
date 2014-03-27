<?php

namespace JsonSchema\Validator\Constraint;

class ArrayConstraint extends AbstractConstraint
{
    private $nestedSchemaValidation = false;
    private $internalType;
    private $uniqueness = false;
    private $minCount;

    public function validate()
    {
        if (!$this->validateType()) {
            return false;
        }

        if (true === $this->nestedSchemaValidation) {

        }

        if (false !== ($typeFunction = $this->getTypeFunction($this->internalType))) {
            foreach ($this->value as $value) {
                if (false === call_user_func($typeFunction, $value)) {
                    return false;
                }
            }
        }

        if (true === $this->uniqueness) {
            $this->value = array_unique($this->value);
        }

        if ((int) $this->minCount > 0) {
            if (count($this->value) < $this->minCount) {
                return false;
            }
        }

        return true;
    }

    public function hasCorrectType()
    {
        return is_array($this->value);
    }

    public function getNestedSchemaValidation()
    {
        return $this->nestedSchemaValidation;
    }

    public function setNestedSchemaValidation($choice)
    {
        $this->nestedSchemaValidation = (bool) $choice;
    }

    public function setInternalType($type)
    {
        $this->internalType = $type;
    }

    public function getInternalType()
    {
        return $this->internalType;
    }

    public function getUniqueness()
    {
        return $this->uniqueness;
    }

    public function setUniqueness($choice)
    {
        $this->uniqueness = (bool) $choice;
    }

    public function setMinimumCount($count)
    {
        $this->minCount = (int) $count;
    }

    public function getMinimumCount()
    {
        return $this->minCount;
    }
}
