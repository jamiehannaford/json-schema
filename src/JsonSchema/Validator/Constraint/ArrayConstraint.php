<?php

namespace JsonSchema\Validator\Constraint;

class ArrayConstraint extends AbstractConstraint
{
    public function hasCorrectType()
    {
        return is_array($this->value);
    }
}