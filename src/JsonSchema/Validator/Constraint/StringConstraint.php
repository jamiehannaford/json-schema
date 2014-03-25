<?php

namespace JsonSchema\Validator\Constraint;

class StringConstraint extends AbstractConstraint
{
    public function hasCorrectType()
    {
        return is_string($this->value);
    }
}