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

    private $jsonPrimitiveType = [
        'string', 'number', 'boolean',
        'null', 'object', 'array'
    ];

    protected $value;
    protected $constraintFactory;
    protected $enumValues;
    protected $type;

    public function __construct(
        $value,
        EventDispatcherInterface $dispatcher,
        ConstraintFactoryInterface $factory = null
    ) {
        $this->setValue($value);
        $this->setEventDispatcher($dispatcher);
        $this->setConstraintFactory($factory ?: new ConstraintFactory());
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setConstraintFactory(ConstraintFactoryInterface $factory)
    {
        $this->constraintFactory = $factory;
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

    private function validateIndividualType($type)
    {
        if (false === ($function = $this->getTypeFunction($type))) {
            return false;
        }

        return call_user_func($function, $this->value);
    }

    public function validateType()
    {
        if ($this->type) {
            if (is_string($this->type)) {
                if (false === $this->validateIndividualType($this->type)) {
                    return false;
                }
            } elseif (is_array($this->type)) {
                $success = false;
                foreach ($this->type as $type) {
                    if (true === $this->validateIndividualType($type)) {
                        $success = true;
                    }
                }
                if (!$success) {
                    return false;
                }
            }

        }

        if ($this->hasCorrectType()) {
            return true;
        }

        $this->registerError('wrongValue');

        return false;
    }

    final public function validate()
    {
        if (true !== $this->validateType()) {
            return false;
        }

        if (is_array($this->enumValues)) {
            if (!in_array($this->value, $this->enumValues)) {
                return false;
            }
        }

        return $this->validateConstraint();
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
        return in_array($value, $this->jsonPrimitiveType);
    }

    public function validateRegex($string)
    {
        return false !== @preg_match($string, null);
    }

    public function setEnumValues(array $enumValues)
    {
        $this->enumValues = $enumValues;
    }

    public function getEnumValues()
    {
        return $this->enumValues;
    }

    private function validateTypeArray($types)
    {
        if (is_array($types)) {
            foreach ($types as $type) {
                if (false === $this->validateType($type)) {
                    return false;
                }
            }
            return true;
        } elseif (is_string($types)) {
            return $this->validatePrimitiveType($types);
        }

        return false;
    }

    public function setType($type)
    {
        if (true !== $this->validateTypeArray($type)) {
            throw new \InvalidArgumentException("Not a valid primitive type");
        }
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
}