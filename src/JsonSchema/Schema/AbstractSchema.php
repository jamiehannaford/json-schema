<?php

namespace JsonSchema\Schema;

use JsonSchema\ArrayAccessTrait;
use JsonSchema\Enum\SchemaKeyword;
use JsonSchema\Validator\Constraint\ConstraintInterface;
use JsonSchema\Validator\ValidatorInterface;

abstract class AbstractSchema implements SchemaInterface
{
    use ArrayAccessTrait;

    private $validator;

    public function __construct(ValidatorInterface $validator, $data)
    {
        $this->setValidator($validator);
        $this->setData($data);
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

    public function getKeywordConstraints($keyword, $value)
    {
        $constraints = [];

        switch ($keyword) {
            case SchemaKeyword::TITLE:
            case SchemaKeyword::DESCRIPTION:
                $constraints[] = $this->validator->createConstraint('StringConstraint', $value);
                break;
            case SchemaKeyword::MULTIPLE_OF:
                $constraint = $this->validator->createConstraint('NumberConstraint', $value);
                $constraint->setLowerBound(0);
                $constraints[] = $constraint;
                break;
            case SchemaKeyword::MAXIMUM:
                $constraints[] = $this->validator->createConstraint('NumberConstraint', $value);
                break;
            case SchemaKeyword::EXCLUSIVE_MAXIMUM:
                $constraints[] = $this->validator->createConstraint('BooleanConstraint', $value);
                break;
        }

        return $constraints;
    }

    private function addValidatorConstraintsForKeyword($keyword, $value)
    {
        $constraints = $this->getKeywordConstraints($keyword, $value);

        foreach ($constraints as $constraint) {
            if ($constraint instanceof ConstraintInterface) {
                $this->validator->addConstraint($constraint);
            }
        }
    }

    private function setKeyword($name, $value)
    {
        $this->addValidatorConstraintsForKeyword($name, $value);

        if (true === $this->validator->validate()) {
            $this->setValue($name, $value);
        }
    }
}