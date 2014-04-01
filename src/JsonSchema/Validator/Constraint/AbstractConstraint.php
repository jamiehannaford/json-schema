<?php

namespace JsonSchema\Validator\Constraint;

use JsonSchema\HasEventDispatcherTrait;
use JsonSchema\Schema\RootSchema;
use JsonSchema\Validator\ErrorHandler\ErrorHandlerInterface;
use JsonSchema\Validator\ErrorHandler\HasErrorHandlerTrait;
use JsonSchema\Validator\SchemaValidator;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
        'numeric' => 'is_numeric',
        'number'  => 'is_numeric',
        'null'    => 'is_null'
    ];

    private $jsonPrimitiveType = ['string', 'number', 'boolean', 'null'];

    protected $value;

    public function __construct($value, EventDispatcherInterface $dispatcher)
    {
        $this->setValue($value);
        $this->setEventDispatcher($dispatcher);
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

    public function createRootSchema($data)
    {
        return new RootSchema(new SchemaValidator($this->eventDispatcher), $data);
    }

    public function validateSchema($data)
    {
        try {
            $schema = $this->createRootSchema($data);
            if (!$schema->isValid()) {
                return false;
            }
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    public function validatePrimitiveType($value)
    {
        return isset($this->jsonPrimitiveType[$value]);
    }

    public function validateRegex($string)
    {
        return false !== @preg_match($string, null);
    }
}