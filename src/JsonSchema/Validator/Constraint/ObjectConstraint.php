<?php

namespace JsonSchema\Validator\Constraint;

class ObjectConstraint extends AbstractConstraint
{
    public function hasCorrectType()
    {
        return is_object($this->value);
    }
}