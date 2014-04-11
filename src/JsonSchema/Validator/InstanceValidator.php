<?php

namespace JsonSchema\Validator;

use JsonSchema\Enum\SchemaKeyword;
use JsonSchema\Enum\StrictnessMode;
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
                $constraint = $this->createNumberConstraint($name, $this->data);
                $constraint->setMultipleOf($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::MAXIMUM:
                $constraint = $this->createNumberConstraint($name, $this->data);
                $constraint->setHigherBound($value);
                if ($this->schema[SchemaKeyword::EXCLUSIVE_MAXIMUM] === true) {
                    $constraint->setExclusive(true);
                }
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::MINIMUM:
                $constraint = $this->createNumberConstraint($name, $this->data);
                $constraint->setLowerBound($value);
                if ($this->schema[SchemaKeyword::EXCLUSIVE_MINIMUM] === true) {
                    $constraint->setExclusive(true);
                }
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::MAX_LENGTH:
                $constraint = $this->createStringConstraint($name, $this->data);
                $constraint->setMaxLength($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::MIN_LENGTH:
                $constraint = $this->createStringConstraint($name, $this->data);
                $constraint->setMinLength($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::PATTERN:
                $constraint = $this->createStringConstraint($name, $this->data);
                $constraint->setRegexValidation($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::ITEMS:
                $constraint = $this->createArrayConstraint($name, $this->data);
                if ($this->schema[SchemaKeyword::ADDITIONAL_ITEMS] === false
                    && is_array($this->schema[SchemaKeyword::ITEMS])
                ) {
                    $constraint->setMaximumCount(count($value));
                }
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::MAX_ITEMS:
                $constraint = $this->createArrayConstraint($name, $this->data);
                $constraint->setMaximumCount($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::MIN_ITEMS:
                $constraint = $this->createArrayConstraint($name, $this->data);
                $constraint->setMinimumCount($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::UNIQUE_ITEMS:
                $constraint = $this->createArrayConstraint($name, $this->data);
                if ($value === true) {
                    $constraint->setUniqueItems(true);
                }
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::MAX_PROPERTIES:
                $constraint = $this->createObjectConstraint($name, $this->data);
                $constraint->setMaxProperties($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::MIN_PROPERTIES:
                $constraint = $this->createObjectConstraint($name, $this->data);
                $constraint->setMinProperties($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::REQUIRED:
                $constraint = $this->createObjectConstraint($name, $this->data);
                $constraint->setRequiredElementNames($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::ADDITIONAL_PROPERTIES:
                $constraint = $this->createObjectConstraint($name, $this->data);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::PROPERTIES:
            case SchemaKeyword::PATTERN_PROPERTIES:
                $constraint = $this->createObjectConstraint($name, $this->data);
                if (false === $this->schema['additionalProperties']) {
                    $constraint->setStrictAdditionalProperties(true);
                    if ($properties = $this->schema['properties']) {
                        $constraint->setAllowedPropertyNames($properties);
                    }
                    if ($patternProperties = $this->schema['patternProperties']) {
                        $constraint->setRegexArray($patternProperties);
                    }
                }
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::DEPENDENCIES:
                $constraint = $this->createObjectConstraint($name, $this->data);
                $constraint->setDependenciesInstanceValidation(true);

                if (is_object($value)) {
                    $constraint->setSchemaDependencies($value);
                } elseif (is_array($value)) {
                    $constraint->setAllowedPropertyNames($value);
                }

                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::ENUM:
                $constraint = $this->createGenericConstraint($name, $this->data);
                $constraint->setEnumValues($value);
                $this->addConstraint($constraint);
                break;
            case SchemaKeyword::TYPE:
                $constraints = [];

                if (is_array($value)) {
                    foreach ($value as $indValue) {
                        $class = $this->createConstraintName($indValue);
                        $constraints[] = $this->createConstraint($class, $name, $this->data);
                    }
                } else {
                    $class = $this->createConstraintName($value);
                    $constraints[] = $this->createConstraint($class, $name, $this->data);
                }

                $this->addConstraint($constraints, StrictnessMode::ANY);
                break;
        }
    }

    private function createConstraintName($value)
    {
        switch (strtolower($value)) {
            case "integer":
            case "number":
            case "numeric":
                $value = "Number";
                break;
            case "bool":
                $value = "Boolean";
                break;
        }
        return sprintf("%sConstraint", ucfirst($value));
    }

    public function validate()
    {
        foreach ($this->schema as $keywordName => $keywordValue) {
            $this->addKeywordConstraints($keywordName, $keywordValue);
        }

        return $this->doValidate();
    }
}