<?php

namespace JsonSchema\Validator\Constraint;

class NumberConstraint extends AbstractConstraint
{
    private $lowerBound;
    private $higherBound;
    private $exclusive = true;
    private $multipleOf = false;

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

        if ((int) $this->multipleOf > 0) {
            if ($this->value % $this->multipleOf !== 0) {
                return false;
            }
        }

        return true;
    }

    public function setLowerBound($lowerBound)
    {
        $this->lowerBound = (int) $lowerBound;
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

    public function getMultipleOf()
    {
        return $this->multipleOf;
    }

    public function setMultipleOf($multipleOf)
    {
        $this->multipleOf = (int) $multipleOf;
    }

    public function setHigherBound($higherBound)
    {
        $this->higherBound = (int) $higherBound;
    }

    public function getHigherBound()
    {
        return $this->higherBound;
    }
}
