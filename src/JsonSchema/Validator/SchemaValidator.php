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
                $constraint = $this->createStringConstraint($keyword, $value);
                $this->addConstraint($constraint);
                break;

            // Regex string
            case SchemaKeyword::PATTERN:
                $constraint = $this->createStringConstraint($keyword, $value);
                $constraint->setRegexValidation(true);
                $this->addConstraint($constraint);
                break;

            // Numeric
            case SchemaKeyword::MINIMUM:
            case SchemaKeyword::MAXIMUM:
                $constraint = $this->createNumberConstraint($keyword, $value);
                $this->addConstraint($constraint);
                break;

            // Integer > 0
            case SchemaKeyword::MULTIPLE_OF:
                $constraint = $this->createNumberConstraint($keyword, $value);
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
                $constraint = $this->createNumberConstraint($keyword, $value);
                $constraint->setLowerBound(0);
                $constraint->setExclusive(false);
                $this->addConstraint($constraint);
                break;

            // Boolean
            case SchemaKeyword::EXCLUSIVE_MINIMUM:
            case SchemaKeyword::EXCLUSIVE_MAXIMUM:
            case SchemaKeyword::UNIQUE_ITEMS:
                $constraint = $this->createBoolConstraint($keyword, $value);
                $this->addConstraint($constraint);
                break;

            // Bool or object
            case SchemaKeyword::ADDITIONAL_PROPERTIES:
            case SchemaKeyword::ADDITIONAL_ITEMS:
                // bool constraint
                $boolConstraint = $this->createBoolConstraint($keyword, $value);

                // object constraint + ensure schema validation
                $objectConstraint = $this->createObjectConstraint($keyword, $value);
                $objectConstraint->setSchemaValidation(true);

                // Make sure the validator knows either constraint can succeed for validation to pass
                $this->addConstraint([$boolConstraint, $objectConstraint], StrictnessMode::ANY);
                break;

            // Object or array
            case SchemaKeyword::ITEMS:
                // Add array constraint + ensure nested schema validation
                $array = $this->createArrayConstraint($keyword, $value);
                $array->setNestedSchemaValidation(true);

                // Add object constraint + ensure schema validation
                $object = $this->createObjectConstraint($keyword, $value);
                $object->setSchemaValidation(true);

                $this->addConstraint([$array, $object], StrictnessMode::ANY);
                break;

            // Array whose items must be unique strings
            case SchemaKeyword::REQUIRED:
                $constraint = $this->createArrayConstraint($keyword, $value);
                $constraint->setInternalType('string');
                $constraint->setUniqueness(true);
                $constraint->setMinimumCount(1);
                $this->addConstraint($constraint);
                break;

            // Object whose values must be valid schemas
            case SchemaKeyword::PROPERTIES:
            case SchemaKeyword::DEFINITIONS:
                $constraint = $this->createObjectConstraint($keyword, $value);
                $constraint->setNestedSchemaValidation(true);
                $this->addConstraint($constraint);
                break;

            // Object whose keys are valid regex strings + values are valid JSON schemas
            case SchemaKeyword::PATTERN_PROPERTIES:
                $constraint = $this->createObjectConstraint($keyword, $value);
                $constraint->setPatternPropertiesValidation(true);
                $this->addConstraint($constraint);
                break;

            // Object whose values are either valid schemas or arrays
            case SchemaKeyword::DEPENDENCIES:
                $constraint = $this->createObjectConstraint($keyword, $value);
                $constraint->setDependenciesSchemaValidation(true);
                $this->addConstraint($constraint);
                break;

            // Array
            case SchemaKeyword::ENUM:
                $constraint = $this->createArrayConstraint($keyword, $value);
                $constraint->setMinimumCount(1);
                $this->addConstraint($constraint);
                break;

            // Array whose string values, or string, which is a primitive type
            case SchemaKeyword::TYPE:
                $arrayConstraint = $this->createArrayConstraint($keyword, $value);
                $arrayConstraint->setMinimumCount(1);
                $arrayConstraint->setInternalPrimitiveTypeValidation(true);
                $arrayConstraint->setUniqueness(true);

                $stringConstraint = $this->createStringConstraint($keyword, $value);
                $stringConstraint->setPrimitiveTypeValidation(true);

                $this->addConstraint([$arrayConstraint, $stringConstraint], StrictnessMode::ANY);
                break;

            // Array whose elements must be valid schemas
            case SchemaKeyword::ALL_OF:
            case SchemaKeyword::ANY_OF:
            case SchemaKeyword::ONE_OF:
                $arrayConstraint = $this->createArrayConstraint($keyword, $value);
                $arrayConstraint->setNestedSchemaValidation(true);
                $this->addConstraint($arrayConstraint);
                break;

            // Object which is valid schema
            case SchemaKeyword::NOT:
                $objectConstraint = $this->createObjectConstraint($keyword, $value);
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