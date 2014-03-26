<?php

namespace JsonSchema\Validator\Constraint;

class NumberConstraint extends AbstractConstraint
{
    private $lowerBound;
    private $exclusive = true;

    public function hasCorrectType()
    {
        return is_numeric($this->value);
    }

    public function validate()
    {
        if (!$this->validateType()) {
            return false;
        }

        if ($this->lowerBound) {
            if ($this->exclusive) {
                return $this->value > $this->lowerBound;
            } else {
                return $this->value >= $this->lowerBound;
            }
        }

        return true;
    }

    public function setLowerBound($lowerBound)
    {
        $this->lowerBound = $lowerBound;
    }

    public function getLowerBound()
    {
        return $this->lowerBound;
    }

    public function setExclusive($choice)
    {
        $this->exclusive = (bool) $choice;
    }

    public function getExclusive()
    {
        return $this->exclusive;
    }
}
