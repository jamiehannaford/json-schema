<?php

namespace JsonSchema\Validator\Constraint;

class GenericConstraint extends AbstractConstraint
{
    public function hasCorrectType()
    {
        return true;
    }

    public function validateConstraint()
    {
        return true;
    }
}
