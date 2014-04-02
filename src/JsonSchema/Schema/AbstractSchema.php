<?php

namespace JsonSchema\Schema;

use JsonSchema\ArrayAccessTrait;
use JsonSchema\Enum\SchemaKeyword;
use JsonSchema\Enum\StrictnessMode;
use JsonSchema\IteratorTrait;
use JsonSchema\Validator\Constraint\ConstraintFactory;
use JsonSchema\Validator\Constraint\ConstraintFactoryInterface;
use JsonSchema\Validator\Constraint\ConstraintInterface;
use JsonSchema\Validator\InstanceValidator;
use JsonSchema\Validator\ValidatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractSchema implements SchemaInterface
{
    use ArrayAccessTrait, IteratorTrait;

    private $validator;

    public function __construct(ValidatorInterface $validator, $data)
    {
        $this->setValidator($validator);
        $this->setData($data);
        $this->rewind();
    }

    public function setData($data)
    {
        if (!is_object($data)) {
            throw new \InvalidArgumentException("Schema data must be provided as an object");
        }

        foreach ($data as $key => $val) {
            $this->offsetSet($key, $val);
        }
    }

    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function getValidator()
    {
        return $this->validator;
    }

    public function isKeyword($key)
    {
        return SchemaKeyword::isValidValue($key);
    }

    public function offsetSet($offset, $value)
    {
        if ($this->isKeyword($offset)) {
            return $this->setKeyword($offset, $value);
        }

        $this->setValue($offset, $value);
    }

    private function setValue($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    private function setKeyword($name, $value)
    {
        if (true === $this->validator->validateKeyword($name, $value)) {
            $this->setValue($name, $value);
        }
    }

    public function isValid()
    {
        return count($this->validator->getErrorCount()) === 0;
    }

    public function validateInstanceData(
        $data,
        EventDispatcher $dispatcher = null,
        ConstraintFactoryInterface $factory = null
    ) {
        $instanceValidator = new InstanceValidator($dispatcher, $factory);
        $instanceValidator->setSchema($this);
        $instanceValidator->setData($data);
        return $instanceValidator->validate();
    }
}