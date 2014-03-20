<?php

namespace spec\JsonSchema\Validator;

use JsonSchema\Validator\Constraint\StringConstraint;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CompositeConstraintValidatorSpec extends ObjectBehavior
{
    function let(StringConstraint $constraint)
    {
        $this->beConstructedWith([$constraint]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\CompositeConstraintValidator');
        $this->shouldImplement('JsonSchema\Validator\ValidatorInterface');
    }

    function it_should_accept_array_of_validator_interfaces(StringConstraint $constraint)
    {
        $constraints = [$constraint];
        $this->setConstraints($constraints);
        $this->getConstraints()->shouldReturn($constraints);
    }
}