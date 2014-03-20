<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Validator\Constraint\AbstractConstraint;
use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AbstractConstraintSpec extends ObjectBehavior
{
    const VALUE = 'Foo';

    function let(BufferErrorHandler $handler)
    {
        $this->beAnInstanceOf('spec\JsonSchema\Validator\Constraint\TestableAbstractConstraint');
        $this->beConstructedWith(self::VALUE, $handler);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\Constraint\AbstractConstraint');
    }

    function it_should_use_has_error_handler_trait(BufferErrorHandler $handler)
    {
        $this->getEventDispatcher()->hasListeners('validation.error')->shouldReturn(true);
    }

    function it_should_have_mutable_value()
    {
        $this->setValue(self::VALUE);
        $this->getValue()->shouldReturn(self::VALUE);
    }
}

class TestableAbstractConstraint extends AbstractConstraint
{
    public function validate() {}
}