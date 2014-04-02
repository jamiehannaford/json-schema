<?php

namespace JsonSchema\Validator;

use JsonSchema\Enum\SchemaKeyword;
use JsonSchema\Enum\StrictnessMode;
use JsonSchema\HasEventDispatcherTrait;

class SchemaValidator extends AbstractValidator
{
    public function addKeywordConstraints($keyword, $value)
    {
        switch ($keyword) {
            // String
            case SchemaKeyword::TITLE:
            case SchemaKeyword::DESCRIPTION:
                $constraint = $this->createConstraint('StringConstraint', $value);
                $this->addConstraint($constraint);
                break;

            // Regex string
            case SchemaKeyword::PATTERN:
                $constraint = $this->createConstraint('StringConstraint', $value);
                $constraint->setRegexValidation(true);
                $this->addConstraint($constraint);
                break;

            // Numeric
            case SchemaKeyword::MINIMUM:
            case SchemaKeyword::MAXIMUM:
                $constraint = $this->createConstraint('NumberConstraint', $value);
                $this->addConstraint($constraint);
                break;

            // Integer > 0
            case SchemaKeyword::MULTIPLE_OF:
                $constraint = $this->createConstraint('NumberConstraint', $value);
                $constraint->setLowerBound(0);
                $this->addConstraint($constraint);
                break;

            // Integer >= 0
            case SchemaKeyword::MAX_LENGTH:
            case SchemaKeyword::MIN_LENGTH:
            case SchemaKeyword::MAX_ITEMS:
            case SchemaKeyword::MIN_ITEMS:
            case SchemaKeyword::MAX_PROPERTIES:
            case SchemaKeyword::MIN_PROPERTIES:
                $constraint = $this->createConstraint('NumberConstraint', $value);
                $constraint->setLowerBound(0);
                $constraint->setExclusive(false);
                $this->addConstraint($constraint);
                break;

            // Boolean
            case SchemaKeyword::EXCLUSIVE_MINIMUM:
            case SchemaKeyword::EXCLUSIVE_MAXIMUM:
            case SchemaKeyword::UNIQUE_ITEMS:
                $constraint = $this->createConstraint('BooleanConstraint', $value);
                $this->addConstraint($constraint);
                break;

            // Bool or object
            case SchemaKeyword::ADDITIONAL_PROPERTIES:
            case SchemaKeyword::ADDITIONAL_ITEMS:
                // Make sure the validator knows either constraint can succeed for validation to pass
                $this->setStrictnessMode(StrictnessMode::ANY);

                // Add bool constraint
                $boolConstraint = $this->createConstraint('BooleanConstraint', $value);
                $this->addConstraint($boolConstraint);

                // Add object constraint + ensure schema validation
                $objectConstraint = $this->createConstraint('ObjectConstraint', $value);
                $objectConstraint->setSchemaValidation(true);
                $this->addConstraint($objectConstraint);
                break;

            // Object or array
            case SchemaKeyword::ITEMS:
                $this->setStrictnessMode(StrictnessMode::ANY);
                // Add array constraint + ensure nested schema validation
                $boolConstraint = $this->createConstraint('ArrayConstraint', $value);
                $boolConstraint->setNestedSchemaValidation(true);
                $this->addConstraint($boolConstraint);

                // Add object constraint + ensure schema validation
                $objectConstraint = $this->createConstraint('ObjectConstraint', $value);
                $objectConstraint->setSchemaValidation(true);
                $this->addConstraint($objectConstraint);
                break;

            // Array whose items must be unique strings
            case SchemaKeyword::REQUIRED:
                $constraint = $this->createConstraint('ArrayConstraint', $value);
                $constraint->setInternalType('string');
                $constraint->setUniqueness(true);
                $constraint->setMinimumCount(1);
                $this->addConstraint($constraint);
                break;

            // Object whose values must be valid schemas
            case SchemaKeyword::PROPERTIES:
            case SchemaKeyword::DEFINITIONS:
                $constraint = $this->createConstraint('ObjectConstraint', $value);
                $constraint->setNestedSchemaValidation(true);
                $this->addConstraint($constraint);
                break;

            // Object whose keys are valid regex strings + values are valid JSON schemas
            case SchemaKeyword::PATTERN_PROPERTIES:
                $constraint = $this->createConstraint('ObjectConstraint', $value);
                $constraint->setPatternPropertiesValidation(true);
                $this->addConstraint($constraint);
                break;

            // Object whose values are either valid schemas or arrays
            case SchemaKeyword::DEPENDENCIES:
                $constraint = $this->createConstraint('ObjectConstraint', $value);
                $constraint->setDependenciesSchemaValidation(true);
                $this->addConstraint($constraint);
                break;

            // Array
            case SchemaKeyword::ENUM:
                $constraint = $this->createConstraint('ArrayConstraint', $value);
                $constraint->setMinimumCount(1);
                $this->addConstraint($constraint);
                break;

            // Array whose string values, or string, which is a primitive type
            case SchemaKeyword::TYPE:
                $arrayConstraint = $this->createConstraint('ArrayConstraint', $value);
                $arrayConstraint->setMinimumCount(1);
                $arrayConstraint->setInternalPrimitiveTypeValidation(true);
                $arrayConstraint->setUniqueness(true);
                $this->addConstraint($arrayConstraint);

                $stringConstraint = $this->createConstraint('StringConstraint', $value);
                $stringConstraint->setPrimitiveTypeValidation(true);
                $this->addConstraint($stringConstraint);
                break;

            // Array whose elements must be valid schemas
            case SchemaKeyword::ALL_OF:
            case SchemaKeyword::ANY_OF:
            case SchemaKeyword::ONE_OF:
                $arrayConstraint = $this->createConstraint('ArrayConstraint', $value);
                $arrayConstraint->setNestedSchemaValidation(true);
                $this->addConstraint($arrayConstraint);
                break;

            // Object which is valid schema
            case SchemaKeyword::NOT:
                $objectConstraint = $this->createConstraint('ObjectConstraint', $value);
                $objectConstraint->setSchemaValidation(true);
                $this->addConstraint($objectConstraint);
                break;
        }
    }

    public function validateKeyword($name, $value)
    {
        $this->addKeywordConstraints($name, $value);

        return $this->validate();
    }

    public function validate()
    {
        return $this->doValidate();
    }
}