<?php

namespace JsonSchema\Validator\Constraint;

class NumberConstraint extends AbstractConstraint
{
    private $lowerBound;
    private $higherBound;
    private $exclusive = false;
    private $multipleOf = false;

    public function hasCorrectType()
    {
        return is_numeric($this->value);
    }

    public function validateConstraint()
    {
        if ($this->lowerBound) {
            $success = ($this->exclusive)
                ? $this->value > $this->lowerBound
                : $this->value >= $this->lowerBound;

            if ($success !== true) {
                $exclusive = ($this->exclusive) ? '' : ' or equal to';
                $message = sprintf("Number must be greater than%s the lower bound", $exclusive);
                return $this->logFailure($message, $this->lowerBound);
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
