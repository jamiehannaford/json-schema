<?php

namespace JsonSchema\Validator\Constraint;

class ArrayConstraint extends AbstractConstraint
{
    private $nestedSchemaValidation = false;
    private $internalType;
    private $uniqueness = false;
    private $minCount;

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
