<?php

namespace JsonSchema\Validator\Constraint;

class NumberConstraint extends AbstractConstraint
{
    const TYPE = 'numeric';

    private $lowerBound;

    public function setLowerBound($lowerBound)
    {
        $this->lowerBound = $lowerBound;
    }

    public function getLowerBound()
    {
        return $this->lowerBound;
    }
}