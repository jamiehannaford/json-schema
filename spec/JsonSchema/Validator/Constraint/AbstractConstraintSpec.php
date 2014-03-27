<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Validator\Constraint\AbstractConstraint;
use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

class AbstractConstraintSpec extends ObjectBehavior
{
    const VALUE = 'Foo';

    function let(EventDispatcher $dispatcher, BufferErrorHandler $handler)
    {
        $this->beAnInstanceOf('spec\JsonSchema\Validator\Constraint\TestableAbstractConstraint');
        $this->beConstructedWith(self::VALUE, $dispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\Constraint\AbstractConstraint');
    }

    function it_should_have_mutable_value()
    {
        $this->setValue(self::VALUE);
        $this->getValue()->shouldReturn(self::VALUE);
    }

    function it_should_allow_easy_creation_of_root_schema_object()
    {
        $data = (object)['foo' => 'bar'];
        $this->createRootSchema($data)->shouldReturnAnInstanceOf('JsonSchema\Schema\SchemaInterface');
    }
}

class TestableAbstractConstraint extends AbstractConstraint
{
    public function hasCorrectType() {}
    public function validate() {}
}