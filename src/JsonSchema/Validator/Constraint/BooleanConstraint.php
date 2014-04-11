<?php

namespace JsonSchema\Validator\Constraint;

class BooleanConstraint extends AbstractConstraint
{
    const TYPE = 'boolean';

    public function hasCorrectType()
    {
        return is_bool($this->value);
    }

    public function validateConstraint()
    {
        // no custom logic
        return true;
    }
}