<?php

namespace JsonSchema\Validator\Constraint;

class NumberConstraint extends AbstractConstraint
{
    private $lowerBound;

    public function hasCorrectType()
    {
        return is_numeric($this->value);
    }

    public function setLowerBound($lowerBound)
    {
        $this->lowerBound = $lowerBound;
    }

    public function getLowerBound()
    {
        return $this->lowerBound;
    }
}