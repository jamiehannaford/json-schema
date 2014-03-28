<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Exception\InvalidTypeException;
use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

class StringConstraintSpec extends ObjectBehavior
{
    function let(EventDispatcher $dispatcher)
    {
        $this->beConstructedWith('Foo', $dispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\Constraint\StringConstraint');
    }

    function it_should_fail_type_check_if_value_type_is_not_string()
    {
        $incorrectTypes = self::getWrongDataTypes('string');

        foreach ($incorrectTypes as $value) {
            $this->setValue($value);
            $this->validateType()->shouldReturn(false);
        }

        fclose($incorrectTypes['resource']);
    }

    function it_should_fail_entire_validation_if_not_string()
    {
        $incorrectTypes = self::getWrongDataTypes('string');

        foreach ($incorrectTypes as $value) {
            $this->setValue($value);
            $this->validate()->shouldReturn(false);
        }

        fclose($incorrectTypes['resource']);
    }

    function it_should_emit_message_on_validation_failure()
    {
        $this->setValue(['Foo']);
        $this->validate();

        $this->getErrorCount()->shouldReturn(1);
    }

    function it_should_support_regex_validation()
    {
        $this->setRegexValidation(true);
        $this->hasRegexValidation()->shouldReturn(true);
    }

    function it_should_fail_validation_for_non_regex_strings()
    {
        $this->setRegexValidation(true);
        $this->setValue('#foo');
        $this->validate()->shouldReturn(false);
    }

    public static function getWrongDataTypes($correctType)
    {
        $allTypes = [
            'string'   => 'foo',
            'int'      => 1,
            'float'    => 2.5,
            'bool'     => true,
            'object'   => new \stdClass(),
            'array'    => [],
            'resource' => fopen('php://temp', 'r+')
        ];

        if ($correctType == 'numeric') {
            $correctType = ['int', 'float'];
        }

        $correctTypes = array_flip((array) $correctType);

        return array_diff_key($allTypes, $correctTypes);
    }

    function it_should_support_primitive_type_validation()
    {
        $this->getPrimitiveTypeValidation()->shouldReturn(false);

        $this->setPrimitiveTypeValidation(true);
        $this->getPrimitiveTypeValidation()->shouldReturn(true);
    }

    function it_should_fail_validation_if_value_is_not_primitive_type_and_option_set()
    {
        $this->setPrimitiveTypeValidation(true);

        $this->setValue('foo');
        $this->validate()->shouldReturn(false);
    }

    function it_should_support_max_length()
    {
        $this->setMaxLength(100);
        $this->getMaxLength()->shouldReturn(100);
    }

    function it_should_fail_validation_if_string_length_is_higher_than_max_length()
    {
        $this->setMaxLength(10);
        $this->setValue(str_repeat('a', 15));

        $this->validate()->shouldReturn(false);
    }

    function it_should_support_min_length()
    {
        $this->setMinLength(100);
        $this->getMinLength()->shouldReturn(100);
    }

    function it_should_default_min_length_to_0()
    {
        $this->getMinLength()->shouldReturn(0);
    }

    function it_should_fail_validation_if_string_length_is_lower_than_min_length()
    {
        $this->setMinLength(100);
        $this->setValue(str_repeat('a', 15));

        $this->validate()->shouldReturn(false);
    }
}