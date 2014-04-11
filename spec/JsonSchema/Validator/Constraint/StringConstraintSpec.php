<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Exception\InvalidTypeException;
use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use JsonSchema\Validator\ErrorHandler\HasErrorHandlerTrait;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\JsonSchema\Validator\HasValidationChecker;
use Symfony\Component\EventDispatcher\EventDispatcher;

class StringConstraintSpec extends ObjectBehavior
{
    use HasValidationChecker;

    const NAME = 'Foo';

    function let(EventDispatcher $dispatcher)
    {
        $this->beConstructedWith(self::NAME, 'bar', $dispatcher);
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
            $this->validateType()->shouldReturn(false);
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

        $this->testFailureDispatch(self::NAME, '#foo', 'Value is not a valid regular expression');
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

        $allowed = ['string', 'number', 'boolean', 'null', 'object', 'array'];
        $this->testFailureDispatch(self::NAME, 'foo', 'Value is not a valid primitive type', $allowed);
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
        $value = str_repeat('a', 15);
        $this->setValue($value);

        $this->testFailureDispatch(self::NAME, $value, 'Value contains more characters than allowed', 10);
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
        $value = str_repeat('a', 15);
        $this->setValue($value);

        $this->testFailureDispatch(self::NAME, $value, 'Value does not contain enough characters', 100);
        $this->validate()->shouldReturn(false);
    }

    function it_should_throw_exception_if_setting_regex_validation_with_invalid_type()
    {
        $this->shouldThrow('InvalidArgumentException')->duringSetRegexValidation('#invalid');
    }

    function it_should_fail_validation_if_string_does_not_match_regex()
    {
        $pattern = '#foo#';
        $this->setRegexValidation($pattern);
        $this->setValue('bar');

        $this->testFailureDispatch(self::NAME, 'bar', 'Value does not satisfy regular expression', $pattern);
        $this->validate()->shouldReturn(false);
    }
}