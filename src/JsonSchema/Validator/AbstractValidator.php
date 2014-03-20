<?php

namespace JsonSchema\Validator;

use JsonSchema\HasEventDispatcherTrait;
use JsonSchema\Validator\Constraint\ConstraintFactory;
use JsonSchema\Validator\Constraint\ConstraintInterface;
use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use JsonSchema\Validator\ErrorHandler\ErrorHandlerInterface;
use JsonSchema\Validator\ErrorHandler\HasErrorHandlerTrait;

abstract class AbstractValidator implements ValidatorInterface
{
    use HasEventDispatcherTrait, HasErrorHandlerTrait;

    protected $data;
    protected $handler;
    protected $constraints = [];

    public function __construct(ErrorHandlerInterface $errorHandler = null)
    {
        $this->setErrorHandler($errorHandler ?: new BufferErrorHandler());
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setErrorHandler(ErrorHandlerInterface $errorHandler)
    {
        $this->handler = $errorHandler;
    }

    public function getErrorHandler()
    {
        return $this->handler;
    }

    public function getConstraintObject($name, $value)
    {
        $factory = new ConstraintFactory();
        return $factory->create($name, $value, $this->handler);
    }

    public function addConstraint(ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;
    }

    public function setConstraints(array $constraints)
    {
        $this->constraints = $constraints;
    }

    public function getConstraints()
    {
        return $this->constraints;
    }
}