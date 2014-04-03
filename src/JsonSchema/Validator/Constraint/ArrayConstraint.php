<?php

namespace JsonSchema\Validator\Constraint;

use JsonSchema\Schema\RootSchema;
use JsonSchema\Validator\SchemaValidator;

class ArrayConstraint extends AbstractConstraint
{
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
               if (!$this->validateSchema($schemaData)) {
                   return false;
               }
            }
        }

        if (false !== ($typeFunction = $this->getTypeFunction($this->internalType))) {
            foreach ($this->value as $value) {
                if (false === call_user_func($typeFunction, $value)) {
                    return false;
                }
            }
        }

        if (true === $this->forceUnique) {
            $this->value = array_unique($this->value);
        }

        if (is_int($this->minCount)) {
            if (count($this->value) < $this->minCount) {
                return false;
            }
        }

        if (is_int($this->maxCount)) {
            if (count($this->value) > $this->maxCount) {
                return false;
            }
        }

        if (true === $this->internalPrimitiveTypeValidation) {
            foreach ($this->value as $value) {
                if (!$this->validatePrimitiveType($value)) {
                    return false;
                }
            }
        }

        if (true === $this->uniqueItems) {
            if ($this->value !== array_unique($this->value)) {
                return false;
            }
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
