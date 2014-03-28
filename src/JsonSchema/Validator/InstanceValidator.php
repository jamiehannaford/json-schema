<?php

namespace JsonSchema\Validator;

use JsonSchema\Enum\SchemaKeyword;
use JsonSchema\Schema\SchemaInterface;

class InstanceValidator extends AbstractValidator
{
    private $schema;

    public function setSchema(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function addKeywordConstraints($name, $value)
    {
        switch ($name) {
            case SchemaKeyword::MULTIPLE_OF:
                $constraint = $this->createConstraint('NumberConstraint', $value);
                $constraint->setMultipleOf($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::MAXIMUM:
                $constraint = $this->createConstraint('NumberConstraint', $value);
                $constraint->setHigherBound($value);
                if ($this->schema[SchemaKeyword::EXCLUSIVE_MAXIMUM] === true) {
                    $constraint->setExclusive(true);
                }
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::MINIMUM:
                $constraint = $this->createConstraint('NumberConstraint', $value);
                $constraint->setLowerBound($value);
                if ($this->schema[SchemaKeyword::EXCLUSIVE_MINIMUM] === true) {
                    $constraint->setExclusive(true);
                }
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::MAX_LENGTH:
                $constraint = $this->createConstraint('StringConstraint', $value);
                $constraint->setMaxLength($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::MIN_LENGTH:
                $constraint = $this->createConstraint('StringConstraint', $value);
                $constraint->setMinLength($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::PATTERN:
                $constraint = $this->createConstraint('StringConstraint', $value);
                $constraint->setRegexValidation(true);
                $this->addConstraint($constraint);
                break;
        }
    }

    public function validate()
    {
        foreach ($this->schema as $keywordName => $keywordValue) {
            $this->addKeywordConstraints($keywordName, $keywordValue);
        }

        return $this->doValidate();
    }
}
