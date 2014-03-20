<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConstraintFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\Constraint\ConstraintFactory');
    }

    function it_should_throw_exception_if_asked_to_create_nonexistent_constraint_class(BufferErrorHandler $handler)
    {
        $exception = new \RuntimeException('JsonSchema\Validator\Constraint\FooConstraint class does not exist');
        $this->shouldThrow($exception)->duringCreate('FooConstraint', 'Foo', $handler);
    }

    function it_should_create_a_constraint_class(BufferErrorHandler $handler)
    {
        $this->create('StringConstraint', 'Foo', $handler)->shouldHaveType('JsonSchema\Validator\Constraint\StringConstraint');
    }

    function it_should_throw_exception_if_asked_to_create_class_that_doesnt_implement_ConstraintInterface(BufferErrorHandler $handler)
    {
        $this->shouldThrow('InvalidArgumentException')->duringCreate('\stdClass', 'Foo', $handler);
    }
}