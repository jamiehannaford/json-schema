<?php

namespace JsonSchema\Schema;

use InvalidArgumentException;
use JsonSchema\ArrayAccessTrait;
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

    public function offsetSet($offset, $value)
    {
        // Are we setting a keyword?
        if ($this->validator->isKeyword($offset)) {

            $setter = $this->validator->getKeywordSetterMethod($offset);

            if ($setter) {
                return call_user_func_array([$this, $setter], [$offset, $value]);
            } else {
                throw new \RuntimeException(sprintf(
                    "No setter method found for %s keyword", $offset
                ));
            }
        }

        // Setting a normal value
        $this->setValue($offset, $value);
    }

    private function setValue($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    private function checkStringValidity($key, $value)
    {
        if (is_array($value) || is_object($value) || is_resource($value)) {
            throw InvalidTypeException::factory($key, $value, "string");
        }
    }

    private function setItemAsString($key, $value)
    {
        $this->checkStringValidity($key, $value);

        $this->setValue($key, (string) $value);
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

    private function setItemAsBoolean($key, $value)
    {
        $this->setValue($key, (bool) $value);
    }

    private function setItemAsNaturalNumber($key, $value)
    {
        $this->setItemAsInteger($key, $value, -1);
    }

    private function setItemAsArray($key, $value)
    {
        $this->setValue($key, (array) $value);
    }

    public function setMultipleOf($value)
    {
        $this->setItemAsInteger('multipleOf', $value, 0);
    }

    public function setPattern($value)
    {
        $this->checkStringValidity('pattern', $value);

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

    private function validateArrayType($value, $key)
    {
        if (!is_array($value)) {
            throw InvalidTypeException::factory($key, $value, 'array');
        }
    }

    public function setRequired($array)
    {
        $this->validateArrayType($array, 'required');
        $this->validateUniqueStringArray($array);

        $this->setItemAsArray('required', $array);
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

    public function isValidSchema($schema, $returnErrors = false)
    {
        if (!is_object($schema)) {
            return false;
        }

        $tmpSchema = new static();
        $errors = [];

        foreach ($schema as $key => $value) {
            if ($this->isKeyword($key)) {
                try {
                    $tmpSchema[$key] = $value;
                } catch (InvalidArgumentException $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        if (empty($errors)) {
            return true;
        } elseif ($returnErrors === true) {
            return $errors;
        } else {
            return false;
        }
    }

    public function validateSchema($schema)
    {
        if (!$this->isValidSchema($schema)) {
            throw new InvalidTypeException('Schema is invalid');
        }
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
        $this->validateArrayType($array, 'enum');

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
        $this->validateArrayType($array, 'anyOf');

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
        $this->validateArrayType($array, 'oneOf');

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

    public function setTitle($value)
    {
        
    }

    public function set($value)
    {

    }
}
