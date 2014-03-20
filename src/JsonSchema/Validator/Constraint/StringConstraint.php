<?php

namespace JsonSchema\Validator\Constraint;

class StringConstraint extends AbstractConstraint
{
    const TYPE = 'string';

    public function validate()
    {
        if (!$this->validateType()) {
            return false;
        }
    }
}