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

    private $typeFunctions = [
        'string'  => 'is_string',
        'bool'    => 'is_bool',
        'boolean' => 'is_bool',
        'array'   => 'is_array',
        'object'  => 'is_object',
        'int'     => 'is_int',
        'integer' => 'is_integer',
        'float'   => 'is_float',
        'numeric' => 'is_numeric'
    ];

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

    abstract public function hasCorrectType();

    protected function registerError($errorType, $expectedValue = null)
    {
        $this->getEventDispatcher()->dispatch('validation.error', new Event([
            'value'     => $this->value,
            'errorType' => $errorType,
            'expected'  => $expectedValue
        ]));
    }

    public function validateType()
    {
        if ($this->hasCorrectType()) {
            return true;
        }

        $this->registerError('wrongValue');

        return false;
    }

    public function validate()
    {
        return $this->validateType();
    }

    protected function getTypeFunction($type)
    {
        return (isset($this->typeFunctions[$type])) ? $this->typeFunctions[$type] : false;
    }
}