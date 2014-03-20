<?php

namespace spec\JsonSchema\Validator;

use JsonSchema\Exception\InvalidTypeException;
use JsonSchema\Exception\ValidationException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SchemaValidatorSpec extends ObjectBehavior
{
    const INVALID_TYPE_EXCEPTION = 'JsonSchema\Exception\InvalidTypeException';

    private function getFixture($name)
    {
        return file_get_contents(__DIR__ . '/../../../fixtures/' . $name);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\SchemaValidator');
        $this->shouldImplement('JsonSchema\Validator\ValidatorInterface');
    }

    function it_should_only_validate_an_object()
    {
        $this->setData([]);
        $exception = new ValidationException("\"JSON schema\" must be an object, you provided an array");
        $this->shouldThrow($exception)->duringValidate();
    }

    function it_should_throw_exception_if_validating_a_non_supported_keyword()
    {
        $this->shouldThrow('InvalidArgumentException')->duringValidateKeyword('foo', 'bar');
    }

    function it_should_return_false_if_querying_a_non_existent_keyword()
    {
        $this->shouldNotBeKeyword('foo');
        $this->getKeywordConstraints('foo')->shouldReturn(null);
    }

    function it_should_fail_non_string_description()
    {

    }
}