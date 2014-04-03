<?php

namespace JsonSchema\Validator\Constraint;

class BooleanConstraint extends AbstractConstraint
{
    public function hasCorrectType()
    {
        return is_bool($this->value);
    }

    public function validateConstraint()
    {
        // no custom logic
    }
}