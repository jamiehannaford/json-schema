<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Exception\InvalidTypeException;
use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StringConstraintSpec extends ObjectBehavior
{
    const VALUE = 'Foo';

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

    function let(BufferErrorHandler $handler)
    {
        $this->beConstructedWith(self::VALUE, $handler);
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
}