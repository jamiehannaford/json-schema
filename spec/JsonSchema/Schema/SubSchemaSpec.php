<?php

namespace spec\JsonSchema\Schema;

use JsonSchema\Validator\SchemaValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SubSchemaSpec extends ObjectBehavior
{
    function let(SchemaValidator $validator)
    {
        $this->beConstructedWith($validator, (object) ['foo' => 'bar']);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf('JsonSchema\Schema\SubSchema');
        $this->shouldImplement('JsonSchema\Schema\SchemaInterface');
    }
}