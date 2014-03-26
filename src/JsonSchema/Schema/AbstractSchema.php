<?php

namespace JsonSchema\Schema;

use JsonSchema\ArrayAccessTrait;
use JsonSchema\Enum\SchemaKeyword;
use JsonSchema\Enum\StrictnessMode;
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
            // String
            case SchemaKeyword::TITLE:
            case SchemaKeyword::DESCRIPTION:
                $constraints[] = $this->validator->createConstraint('StringConstraint', $value);
                break;

            // Regex string
            case SchemaKeyword::PATTERN:
                $constraint = $this->validator->createConstraint('StringConstraint', $value);
                $constraint->setRegexValidation(true);
                $constraints[] = $constraint;
                break;

            // Numeric
            case SchemaKeyword::MINIMUM:
            case SchemaKeyword::MAXIMUM:
                $constraints[] = $this->validator->createConstraint('NumberConstraint', $value);
                break;

            // Integer > 0
            case SchemaKeyword::MULTIPLE_OF:
                $constraint = $this->validator->createConstraint('NumberConstraint', $value);
                $constraint->setLowerBound(0);
                $constraints[] = $constraint;
                break;

            // Integer >= 0
            case SchemaKeyword::MAX_LENGTH:
            case SchemaKeyword::MIN_LENGTH:
            case SchemaKeyword::MAX_ITEMS:
            case SchemaKeyword::MIN_ITEMS:
            case SchemaKeyword::MAX_PROPERTIES:
            case SchemaKeyword::MIN_PROPERTIES:
                $constraint = $this->validator->createConstraint('NumberConstraint', $value);
                $constraint->setLowerBound(0);
                $constraint->setExclusive(false);
                $constraints[] = $constraint;

            // Boolean
            case SchemaKeyword::EXCLUSIVE_MINIMUM:
            case SchemaKeyword::EXCLUSIVE_MAXIMUM:
            case SchemaKeyword::UNIQUE_ITEMS:
                $constraints[] = $this->validator->createConstraint('BooleanConstraint', $value);
                break;

            // Bool or object
            case SchemaKeyword::ADDITIONAL_ITEMS:
                // Make sure the validator knows either constraint can succeed for validation to pass
                $this->validator->setStrictnessMode(StrictnessMode::ANY);
                // Add bool constraint
                $constraints[] = $this->validator->createConstraint('BooleanConstraint', $value);
                // Add object constraint + ensure schema validation
                $objectConstraint = $this->validator->createConstraint('ObjectConstraint', $value);
                $objectConstraint->setSchemaValidation(true);
                $constraints[] = $objectConstraint;
                break;

            // Object or array
            case SchemaKeyword::ITEMS:
                $this->validator->setStrictnessMode(StrictnessMode::ANY);
                // Add array constraint + ensure nested schema validation
                $constraint = $this->validator->createConstraint('ArrayConstraint', $value);
                $constraint->setNestedSchemaValidation(true);
                $constraints[] = $constraint;
                // Add object constraint + ensure schema validation
                $objectConstraint = $this->validator->createConstraint('ObjectConstraint', $value);
                $objectConstraint->setSchemaValidation(true);
                $constraints[] = $objectConstraint;
                break;

            case SchemaKeyword::REQUIRED:
                $constraint = $this->validator->createConstraint('ArrayConstraint', $value);
                $constraint->setInternalType('string');
                $constraint->setUniqueness(true);
                $constraint->setMinimumCount(1);
                $constraints[] = $constraint;
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