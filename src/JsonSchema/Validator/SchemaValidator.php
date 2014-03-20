<?php

namespace JsonSchema\Validator;

use JsonSchema\Exception\InvalidTypeException;
use JsonSchema\Exception\ValidationException;
use JsonSchema\Validator\Constraint\ConstraintFactory;

class SchemaValidator extends AbstractValidator
{
    private $keywords = [
        'title'            => 'string',
        'description'      => 'string',
        'multipleOf'       => 'setMultipleOf',
        'maximum'          => 'integer',
        'exclusiveMaximum' => 'boolean',
        'minimum'          => 'integer',
        'exclusiveMinimum' => 'boolean',
        'minLength'        => 'naturalNumber',
        'maxLength'        => 'naturalNumber',
        'pattern'          => 'setPattern',
        'additionalItems'  => 'setAdditionalItems',
        'items'            => 'setItems',
        'maxItems'         => 'naturalNumber',
        'minItems'         => 'naturalNumber',
        'uniqueItems'      => 'boolean',
        'maxProperties'    => 'naturalNumber',
        'minProperties'    => 'naturalNumber',
        'required'         => 'setRequired',
        'additionalProperties' => 'setAdditionalProperties',
        'properties'           => 'setProperties',
        'dependencies'         => 'setDependencies',
        'enum'             => 'setEnum',
        'type'             => 'setType',
        'anyOf'            => 'setAnyOf',
        'oneOf'            => 'setOneOf',
        'definitions'      => 'setDefinitions',
        'format'           => 'setFormat'
    ];

    public function validate()
    {
        if (!is_object($this->data)) {
            throw InvalidTypeException::factory('JSON schema', $this->data, 'object');
        }


    }

    public function isKeyword($key)
    {
        return isset($this->keywords[$key]);
    }

    public function getKeywordConstraints($keyword)
    {
        if (!$this->isKeyword($keyword)) {
            return null;
        }

        return (array) $this->keywords[$keyword];
    }

    public function getKeywordSetterMethod()
    {

    }

    public function validateKeyword($keyword, $value)
    {
        if (!$this->isKeyword($keyword)) {
            throw new \InvalidArgumentException(sprintf("%s is not a valid keyword", $keyword));
        }

        $constraints = $this->getKeywordConstraints($keyword);

        $factory = new ConstraintFactory();

        foreach ($constraints as $constraintName) {

            $constraint = $factory->create($constraintName);

            if (false === ($result = $constraint->validate($value))) {
                $errors[] = $result;
                $this->declareValidationFailure($keyword, $value, $constraintName, $result);
            }
        }

        return true;
    }

    protected function declareValidationFailure($keyword, $value, $constraintName, $result)
    {

    }
}
