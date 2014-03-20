<?php

namespace JsonSchema\Schema;

use InvalidArgumentException;
use JsonSchema\ArrayAccessTrait;
use JsonSchema\Enum\SchemaKeyword;
use JsonSchema\Exception\InvalidTypeException;
use JsonSchema\Validator\ValidatorInterface;

abstract class AbstractSchema implements SchemaInterface
{
    use ArrayAccessTrait;

    private $approvedFormats = [
        'date-time', 'email', 'hostname', 'ipv4', 'ipv6', 'uri'
    ];

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function isKeyword($key)
    {
        return SchemaKeyword::isValidValue($key);
    }

    private function stringToCamelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    public function offsetSet($offset, $value)
    {
        // Are we setting a keyword?
        if ($this->isKeyword($offset)) {

            $setter = sprintf("set%s", $this->stringToCamelCase($offset));

            if (method_exists($this, $setter)) {
                return $this->{$setter}($value);
            } else {
                throw new \RuntimeException(sprintf(
                    "No setter method found for %s keyword", $offset
                ));
            }
        }

        $this->setValue($offset, $value);
    }

    private function setValue($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    private function setItemAsInteger($key, $value, $min = false, $max = false)
    {
        if (!is_numeric($value)) {
            throw InvalidTypeException::factory($key, $value, "numeric value");
        }

        if ($min !== false && $value <= $min) {
            $message = sprintf(
                "\"%s\" must be a positive integer greater than %s, you provided %s",
                $key, $min, $value
            );
        }

        if ($max !== false && $value >= $max) {
            $message = sprintf(
                "\"%s\" must be a positive integer less than %d, you provided %s",
                $key, $max, $value
            );
        }

        if (isset($message)) {
            throw new InvalidArgumentException($message);
        }

        $this->setValue($key, (int) $value);
    }

    private function validateUniqueStringArray(&$array)
    {
        $errorTypes = [];

        // Prohibit non-string values
        foreach ($array as $key => $value) {
            if (!is_string($value)) {
                unset($array[$key]);
                $errorTypes[] = gettype($value);
            }
        }

        // Ensure values are unique and reset keys
        $array = array_values(array_unique($array));

        if (count($errorTypes)) {
            throw new InvalidArgumentException(sprintf(
                "The array specified is invalid. It must contain a list of "
                . "unique strings. You provided these erroneous types: %s",
                implode(', ', array_unique($errorTypes))
            ));
        }
    }

    public function isValidSchema($schema, $returnErrors = false)
    {
        return true;
    }

    public function validateSchema($schema)
    {
        if (!$this->isValidSchema($schema)) {
            throw new InvalidTypeException('Schema is invalid');
        }
    }

    /******** SETTERS ********/

    public function setTitle($value)
    {
        $constraint = $this->validator->getConstraint('StringConstraint', $value);

        $this->validator->addConstraint($constraint);
        $this->validator->validate();

        $this->setValue('title', $value);
    }

    public function setDescription($value)
    {

    }

    public function setMultipleOf($value)
    {
        $this->setItemAsInteger('multipleOf', $value, 0);
    }

    public function setMaximum($value)
    {

    }

    public function setExclusiveMaximum($value)
    {

    }

    public function setMinimum($value)
    {

    }

    public function setExclusiveMinimum($value)
    {

    }

    public function setMinLength($value)
    {

    }

    public function setMaxLength($value)
    {

    }

    public function setPattern($value)
    {
        //$this->checkStringValidity('pattern', $value);

        if (false === @preg_match($value, null)) {
            throw new InvalidArgumentException(sprintf(
                "The string your provided is invalid regex pattern: %s",
                $value
            ));
        }

        $this->setValue('pattern', $value);
    }

    public function setAdditionalItems($value)
    {
        if (is_object($value)) {
            // @todo Implement schema validation
        } else {
            $value = (bool) $value;
        }

        $this->setValue('additionalItems', $value);
    }

    public function setItems($value)
    {
        if (!is_object($value) && !is_array($value)) {
            throw InvalidTypeException::factory('items', $value, 'object or array');
        }

        $this->setValue('items', $value);
    }

    public function setMaxItems($value)
    {

    }

    public function setMinItems($value)
    {

    }

    public function setUniqueItems($value)
    {

    }

    public function setMaxProperties($value)
    {

    }

    public function setMinProperties($value)
    {

    }

    public function setRequired($array)
    {
        //$this->validateArrayType($array, 'required');
        $this->validateUniqueStringArray($array);

        $this->setValue('required', $array);
    }

    public function setAdditionalProperties($value)
    {
        if (is_object($value)) {
            $this->validateSchema($value);
        } else {
            $value = (bool) $value;
        }

        $this->setValue('additionalProperties', $value);
    }

    public function setProperties($value)
    {
        if (!is_object($value) && !is_array($value)) {
            throw InvalidTypeException::factory('properties', $value, 'object or array');
        }

        $this->setValue('properties', $value);
    }

    public function setDependencies($array)
    {
        if (!is_object($array)) {
            throw InvalidTypeException::factory('dependencies', $array, 'object');
        }

        foreach ($array as $key => &$value) {
            if (is_object($value)) {
                $this->validateSchema($value);
            } elseif (is_array($value)) {
                $this->validateUniqueStringArray($value);
            } else {
                throw new InvalidTypeException(sprintf(
                    "\"dependencies\" should be an object whose values are either "
                    . "objects or arrays. One of the values you provided was a %s",
                    gettype($value)
                ));
            }
        }
    }

    public function setEnum($array)
    {
        //$this->validateArrayType($array, 'enum');

        $this->setValue('enum', $array);
    }

    public function setType($value)
    {
        if (!is_string($value) && !is_array($value)) {
            throw InvalidTypeException::factory('type', $value, 'string or array');
        }

        $this->setValue('type', $value);
    }

    public function setAnyOf($array)
    {
        //$this->validateArrayType($array, 'anyOf');

        foreach ($array as $key => $value) {
            if (is_object($value)) {
                $this->validateSchema($value);
            } else {
                throw new InvalidTypeException(sprintf(
                    "\"anyOf\" should be an array whose values are valid schema "
                    . "objects. One of the values you provided was a %s",
                    gettype($value)
                ));
            }
        }

        $this->setValue('anyOf', $array);
    }

    public function setOneOf($array)
    {
        //$this->validateArrayType($array, 'oneOf');

        foreach ($array as $value) {
            if (is_object($value)) {
                $this->validateSchema($value);
            } else {
                throw new InvalidTypeException(sprintf(
                    "\"oneOf\" should be an array whose values are valid schema "
                    . "objects. One of the values you provided was a %s",
                    gettype($value)
                ));
            }
        }

        $this->setValue('oneOf', $array);
    }

    public function setDefinitions($value)
    {
        if (!is_object($value)) {
            throw InvalidTypeException::factory('definitions', $value, 'object');
        }

        $this->setValue('definitions', $value);
    }

    public function setFormat($value)
    {
        if (!in_array($value, $this->approvedFormats)) {
            throw InvalidTypeException::factory('format', $value, sprintf(
                "one of [%s]", implode(',', $this->approvedFormats)
            ));
        }

        $this->setValue('format', $value);
    }
}
