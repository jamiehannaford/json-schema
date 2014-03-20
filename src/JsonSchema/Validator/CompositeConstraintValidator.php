<?php

namespace JsonSchema\Validator;

class CompositeConstraintValidator extends AbstractValidator
{
    protected $constraints;

    public function __construct(array $constraints)
    {
        $this->setConstraints($constraints);
    }

    public function setConstraints(array $constraints)
    {
        $this->constraints = $constraints;
    }

    public function getConstraints()
    {
        return $this->constraints;
    }

    public function validate()
    {

    }
}