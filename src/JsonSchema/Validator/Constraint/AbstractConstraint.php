<?php

namespace JsonSchema\Validator\Constraint;

use JsonSchema\Enum\LogType;
use JsonSchema\HasEventDispatcherTrait;
use JsonSchema\Schema\RootSchema;
use JsonSchema\Validator\ErrorHandler\HasErrorHandlerTrait;
use JsonSchema\Validator\FailureEvent;
use JsonSchema\Validator\SchemaValidator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractConstraint implements ConstraintInterface
{
    use HasEventDispatcherTrait, HasErrorHandlerTrait;

    const TYPE = '';

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

    protected $jsonPrimitiveType = [
        'string', 'number', 'boolean',
        'null', 'object', 'array'
    ];

    protected $value;
    protected $name;
    protected $constraintFactory;
    protected $enumValues;
    protected $type;
    protected $logType = LogType::EMITTING;
    protected $validationErrors = [];

    public function __construct(
        $name,
        $value,
        EventDispatcherInterface $dispatcher,
        ConstraintFactoryInterface $factory = null
    ) {
        $this->setValue($value);
        $this->setName($name);
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

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setConstraintFactory(ConstraintFactoryInterface $factory)
    {
        $this->constraintFactory = $factory;
    }

    abstract public function hasCorrectType();

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
                    return $this->logFailure("Type is incorrect", $this->type);
                }
            } elseif (is_array($this->type)) {
                $success = false;
                foreach ($this->type as $type) {
                    if (true === $this->validateIndividualType($type)) {
                        $success = true;
                    }
                }
                if (!$success) {
                    return $this->logFailure("Type is incorrect", implode(',', $this->type));
                }
            }

        }

        if ($this->hasCorrectType()) {
            return true;
        }

        $this->logFailure('Type is incorrect', static::TYPE);

        return false;
    }

    final public function validate()
    {
        if (true !== $this->validateType()) {
            return false;
        }

        if (is_array($this->enumValues)) {
            if (!in_array($this->value, $this->enumValues)) {
                return $this->logFailure("Value does not match enum array");
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
            if (!$this->createRootSchema($data)->isValid()) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
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

    public function dispatchError(array $error)
    {
        $this->getEventDispatcher()->dispatch('validation.error', new FailureEvent($error));
    }

    public function logFailure($message, $expectation = null, $value = null, $name = null)
    {
        $error = [
            'name'     => ($name !== null) ? $name : $this->name,
            'value'    => ($value !== null) ? $value : $this->value,
            'message'  => $message,
            'expected' => $expectation
        ];

        if ($this->logType == LogType::EMITTING) {
            $this->dispatchError($error);
        } elseif ($this->logType == LogType::INTERNAL) {
            $this->validationErrors[] = $error;
        }

        return false;
    }

    public function setLogType($type)
    {
        $this->logType = $type;
    }

    public function getLogType()
    {
        return $this->logType;
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    public function flushInternalErrors()
    {
        foreach ($this->validationErrors as $key => $error) {
            // despatch and remove
            $this->dispatchError($error);
            unset($this->validationErrors[$key]);
        }
    }
}