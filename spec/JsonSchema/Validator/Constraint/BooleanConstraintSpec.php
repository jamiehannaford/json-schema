<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\JsonSchema\Validator\HasValidationChecker;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BooleanConstraintSpec extends ObjectBehavior
{
    use HasValidationChecker;

    const NAME = 'Foo';

    function let(EventDispatcher $dispatcher)
    {
        $this->beConstructedWith(self::NAME, true, $dispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\Constraint\BooleanConstraint');
        $this->shouldImplement('JsonSchema\Validator\Constraint\ConstraintInterface');
    }

    function it_should_pass_bool_types()
    {
        $this->setValue(true);
        $this->shouldHaveCorrectType();
        $this->validate()->shouldReturn(true);
    }

    function it_should_fail_non_bool_types()
    {
        $this->setValue('Foo');
        $this->shouldNotHaveCorrectType();

        $this->testFailureDispatch(self::NAME, 'Foo', 'Type is incorrect', 'boolean');
        $this->validate()->shouldReturn(false);
    }
}