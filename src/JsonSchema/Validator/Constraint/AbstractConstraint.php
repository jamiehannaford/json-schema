<?php

namespace JsonSchema\Validator\Constraint;

use JsonSchema\HasEventDispatcherTrait;
use JsonSchema\Validator\ErrorHandler\ErrorHandlerInterface;
use JsonSchema\Validator\ErrorHandler\HasErrorHandlerTrait;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractConstraint implements ConstraintInterface
{
    use HasEventDispatcherTrait, HasErrorHandlerTrait;

    const TYPE = '';

    protected $value;

    public function __construct($value, ErrorHandlerInterface $errorHandler)
    {
        $this->setValue($value);

        $this->setEventDispatcher(new EventDispatcher());
        $this->setErrorHandler($errorHandler);
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function validateType()
    {
        if (strtolower(gettype($this->value)) === static::TYPE) {
            return true;
        }

        $event = new Event([
            'value'     => $this->value,
            'errorType' => 'wrongValue',
            'expected'  => static::TYPE
        ]);
        $this->getEventDispatcher()->dispatch('validation.error', $event);

        return false;
    }

    public function validate()
    {
        return $this->validateType();
    }
}