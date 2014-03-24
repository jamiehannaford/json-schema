<?php

namespace JsonSchema\Validator;

use JsonSchema\HasEventDispatcherTrait;

class SchemaValidator extends AbstractValidator
{
    public function validate()
    {
        foreach ($this->constraints as $constraint) {
            if (!$constraint->validate()) {
                return false;
            }
        }

        return true;
    }
}