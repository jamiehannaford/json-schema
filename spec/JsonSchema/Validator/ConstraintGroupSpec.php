<?php

namespace spec\JsonSchema\Validator;

use JsonSchema\Enum\SchemaKeyword;
use JsonSchema\Enum\StrictnessMode;
use JsonSchema\Validator\Constraint\ArrayConstraint;
use JsonSchema\Validator\Constraint\BooleanConstraint;
use JsonSchema\Validator\Constraint\NumberConstraint;
use JsonSchema\Validator\Constraint\ObjectConstraint;
use JsonSchema\Validator\Constraint\StringConstraint;
use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ConstraintGroupSpec extends ObjectBehavior
{
    use HasValidationChecker;

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\ConstraintGroup');
    }

    function it_should_support_strictness_mode()
    {
        $mode = StrictnessMode::ANY;
        $this->setStrictnessMode($mode);
        $this->getStrictnessMode()->shouldReturn($mode);
    }

    function it_should_default_strictness_mode_to_any()
    {
        $this->getStrictnessMode()->shouldReturn(StrictnessMode::ANY);
    }

    function it_should_throw_exception_if_setting_invalid_mode()
    {
        $this->shouldThrow('InvalidArgumentException')->duringSetStrictnessMode('foo');
    }

    function it_should_allow_addition_of_constraint(StringConstraint $constraint)
    {
        $this->addConstraint($constraint);
        $this->getConstraints()->shouldContain($constraint);

        $this->setConstraints([]);
        $this->getConstraints()->shouldHaveCount(0);
    }

    function it_should_pass_validation_if_no_constraints()
    {
        $this->validate()->shouldReturn(true);
    }

    function it_considers_validation_successful_if_every_constraint_passes_when_strictness_mode_is_all(
        ArrayConstraint $array
    )
    {
        // Set up collab promises
        $array->validateType()->willReturn(true);
        $array->validateConstraint()->willReturn(true);

        $this->setStrictnessMode(StrictnessMode::ALL);
        $this->addConstraint($array);

        $this->validate()->shouldReturn(true);
    }

    function it_considers_validation_unsuccessful_if_any_constraint_fails_when_strictness_mode_is_all(
        StringConstraint $string, BooleanConstraint $bool
    )
    {
        $string->validateType()->willReturn(true);
        $string->validateConstraint()->willReturn(false);

        $bool->validateType()->willReturn(true);
        $bool->validateConstraint()->willReturn(true);

        $this->setStrictnessMode(StrictnessMode::ALL);
        $this->addConstraint($string);
        $this->addConstraint($bool);

        $this->validate()->shouldReturn(false);
    }

    function it_considers_validation_successful_if_any_constraint_passes_when_strictness_mode_is_any(
        ObjectConstraint $object, NumberConstraint $number, BooleanConstraint $bool
    )
    {
        $object->validateType()->willReturn(true);
        $object->validateConstraint()->willReturn(true);
        $this->addConstraint($object);

        $number->validateType()->willReturn(false);
        $number->validateConstraint()->willReturn(false);
        $this->addConstraint($number);

        $bool->validateType()->willReturn(false);
        $bool->validateConstraint()->willReturn(false);
        $this->addConstraint($bool);

        $this->setStrictnessMode(StrictnessMode::ANY);

        $this->validate()->shouldReturn(true);
    }

    function it_considers_validation_unsuccessful_if_all_constraints_fail_when_strictness_mode_is_any(
        ObjectConstraint $object, BooleanConstraint $bool, NumberConstraint $number
    )
    {
        $object->validateType()->willReturn(false);
        $object->validateConstraint()->willReturn(false);
        $this->addConstraint($object);

        $number->validateType()->willReturn(false);
        $number->validateConstraint()->willReturn(false);
        $this->addConstraint($number);

        $bool->validateType()->willReturn(false);
        $bool->validateConstraint()->willReturn(false);
        $this->addConstraint($bool);

        $this->setStrictnessMode(StrictnessMode::ANY);

        $this->validate()->shouldReturn(false);
    }

    function it_should_not_emit_every_error_if_strictness_mode_is_any_and_at_least_1_constraint_passes(
        ObjectConstraint $object,
        StringConstraint $string,
        EventDispatcher $dispatcher,
        BufferErrorHandler $handler
    )
    {
        $dispatcher->addListener('validation.error', [$handler, 'receiveError']);
        $dispatcher->dispatch(Argument::any(), Argument::any())->shouldNotBeCalled();

        $object->validateType()->willReturn(false);
        $object->validateConstraint()->willReturn(false);
        $object->setEventDispatcher($dispatcher);
        $this->addConstraint($object);

        $string->validateType()->willReturn(true);
        $string->validateConstraint()->willReturn(true);
        $object->setEventDispatcher($dispatcher);
        $this->addConstraint($string);

        $this->setStrictnessMode(StrictnessMode::ANY);

        var_dump($handler->getWrappedObject());die;
    }
}
