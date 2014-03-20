<?php

namespace spec\JsonSchema\Schema;

use JsonSchema\Exception\InvalidTypeException;
use JsonSchema\Schema\AbstractSchema;
use JsonSchema\Validator\SchemaValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use InvalidArgumentException;
use spec\JsonSchema\TestHelper;

class AbstractSchemaSpec extends ObjectBehavior
{
    const RANDOM_STRING = 'Foo';
    const TYPE_EXCEPTION = 'JsonSchema\Exception\InvalidTypeException';

    function let(SchemaValidator $validator)
    {
        $this->beAnInstanceOf('spec\JsonSchema\Schema\TestableAbstractSchema');
        $this->beConstructedWith($validator);
    }
}

class TestableAbstractSchema extends AbstractSchema
{
}