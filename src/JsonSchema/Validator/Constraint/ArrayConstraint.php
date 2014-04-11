<?php

namespace JsonSchema\Validator\Constraint;

use JsonSchema\Schema\RootSchema;
use JsonSchema\Validator\SchemaValidator;

class ArrayConstraint extends AbstractConstraint
{
    const TYPE = 'array';

    private $nestedSchemaValidation = false;
    private $internalType;
    private $forceUnique = false;
    private $minCount = 0;
    private $maxCount;
    private $internalPrimitiveTypeValidation = false;
    private $uniqueItems = false;

    public function validateConstraint()
    {
        if (true === $this->nestedSchemaValidation) {
            foreach ($this->value as $schemaData) {
               if (true !== $this->validateSchema($schemaData)) {
                   return $this->logFailure("The nested schemas provided were invalid");
               }
            }
        }

        if (false !== ($typeFunction = $this->getTypeFunction($this->internalType))) {
            foreach ($this->value as $value) {
                if (false === call_user_func($typeFunction, $value)) {
                    return $this->logFailure("The values of this array are of an invalid type", $this->internalType);
                }
            }
        }

        if (true === $this->forceUnique) {
            $this->value = array_unique($this->value);
        }

        if (is_int($this->minCount) && count($this->value) < $this->minCount) {
            return $this->logFailure('Array does not contain enough elements', $this->minCount);
        }

        if (is_int($this->maxCount) && count($this->value) > $this->maxCount) {
            return $this->logFailure('Array contains more elements than is allowed', $this->maxCount);
        }

        if (true === $this->internalPrimitiveTypeValidation) {
            foreach ($this->value as $value) {
                if (!$this->validatePrimitiveType($value)) {
                    return $this->logFailure('Array elements do not match expected type');
                }
            }
        }

        if (true === $this->uniqueItems && $this->value !== array_unique($this->value)) {
            return $this->logFailure('Array is not unique');
        }

        return true;
    }

    public function hasCorrectType()
    {
        return is_array($this->value);
    }

    public function getNestedSchemaValidation()
    {
        return $this->nestedSchemaValidation;
    }

    public function setNestedSchemaValidation($choice)
    {
        $this->nestedSchemaValidation = (bool) $choice;
    }

    public function setInternalType($type)
    {
        $this->internalType = $type;
    }

    public function getInternalType()
    {
        return $this->internalType;
    }

    public function getUniqueness()
    {
        return $this->forceUnique;
    }

    public function setUniqueness($choice)
    {
        $this->forceUnique = (bool) $choice;
    }

    public function setMinimumCount($count)
    {
        $this->minCount = (int) $count;
    }

    public function getMinimumCount()
    {
        return $this->minCount;
    }

    public function setMaximumCount($count)
    {
        $this->maxCount = (int) $count;
    }

    public function getMaximumCount()
    {
        return $this->maxCount;
    }

    public function getInternalPrimitiveTypeValidation()
    {
        return $this->internalPrimitiveTypeValidation;
    }

    public function setInternalPrimitiveTypeValidation($choice)
    {
        $this->internalPrimitiveTypeValidation = (bool) $choice;
    }

    public function setUniqueItems($choice)
    {
        $this->uniqueItems = (bool) $choice;
    }

    public function getUniqueItems()
    {
        return $this->uniqueItems;
    }
}
