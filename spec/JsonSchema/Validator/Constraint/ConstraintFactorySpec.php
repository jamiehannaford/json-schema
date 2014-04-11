<?php

namespace spec\JsonSchema\Validator\Constraint;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ConstraintFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\Constraint\ConstraintFactory');
    }

    function it_should_throw_exception_if_asked_to_create_nonexistent_constraint_class(EventDispatcher $dispatcher)
    {
        $exception = new \RuntimeException('JsonSchema\Validator\Constraint\FooConstraint class does not exist');
        $this->shouldThrow($exception)->duringCreate('FooConstraint', 'Foo', 'Bar', $dispatcher);
    }

    function it_should_create_a_constraint_class(EventDispatcher $dispatcher)
    {
        $this->create('StringConstraint', 'Foo', 'Bar', $dispatcher)->shouldHaveType('JsonSchema\Validator\Constraint\StringConstraint');
    }

    function it_should_throw_exception_if_asked_to_create_class_that_doesnt_implement_ConstraintInterface(EventDispatcher $dispatcher)
    {
        $this->shouldThrow('InvalidArgumentException')->duringCreate('\stdClass', 'Foo', 'Bar', $dispatcher);
    }
}