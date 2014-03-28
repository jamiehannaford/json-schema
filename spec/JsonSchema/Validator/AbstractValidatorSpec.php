<?php

namespace spec\JsonSchema\Validator;

use JsonSchema\Enum\StrictnessMode;
use JsonSchema\Validator\AbstractValidator;
use JsonSchema\Validator\Constraint\ArrayConstraint;
use JsonSchema\Validator\Constraint\BooleanConstraint;
use JsonSchema\Validator\Constraint\NumberConstraint;
use JsonSchema\Validator\Constraint\ObjectConstraint;
use JsonSchema\Validator\Constraint\StringConstraint;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class AbstractValidatorSpec extends ObjectBehavior
{
    public function getMatchers()
    {
        return [
            'contain' => function ($subject, $key) {
                    return array_search($key, $subject);
                },
            'beCount' => function ($subject, $num) {
                    return count($subject) === $num;
                }
        ];
    }

    function let(EventDispatcher $dispatcher)
    {
        $this->beAnInstanceOf('spec\JsonSchema\Validator\TestableAbstractValidator');
        $this->beConstructedWith($dispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\AbstractValidator');
        $this->shouldImplement('JsonSchema\Validator\ValidatorInterface');
    }

    function it_should_set_data()
    {
        $value = ['foo' => 'bar'];
        $this->setData($value)->shouldBeNull();
        $this->getData()->shouldReturn($value);
    }

    function it_should_have_dispatcher(EventDispatcher $dispatcher)
    {
        $this->setEventDispatcher($dispatcher);
        $this->getEventDispatcher()->shouldReturnAnInstanceOf('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    function it_should_allow_addition_of_constraint(StringConstraint $constraint)
    {
        $this->addConstraint($constraint);
        $this->getConstraints()->shouldContain($constraint);

        $this->setConstraints([]);
        $this->getConstraints()->shouldBeCount(0);
    }

    function it_should_provide_easy_instantiation_of_constraint_classes()
    {
         $this->createConstraint('StringConstraint', 'Foo')->shouldReturnAnInstanceOf('JsonSchema\Validator\Constraint\ConstraintInterface');
    }

    function it_should_set_strictness_mode_as_all_by_default()
    {
        $this->getStrictnessMode()->shouldReturn(StrictnessMode::ALL);
    }

    function it_should_allow_different_strictness_modes()
    {
        $this->setStrictnessMode(StrictnessMode::ANY);
        $this->getStrictnessMode()->shouldReturn(StrictnessMode::ANY);
    }

    function it_should_pass_validation_if_no_constraints()
    {
        $this->setData('foo');
        $this->validate()->shouldReturn(true);
    }

    function it_considers_validation_successful_if_every_constraint_passes_when_strictness_mode_is_all(
        ArrayConstraint $array
    )
    {
        // Set up collab promises
        $array->validate()->willReturn(true);

        $this->setData([]);

        $this->setStrictnessMode(StrictnessMode::ALL);
        $this->addConstraint($array);

        $this->validate()->shouldReturn(true);
    }

    function it_considers_validation_unsuccessful_if_any_constraint_fails_when_strictness_mode_is_all(
        StringConstraint $string, BooleanConstraint $bool
    )
    {
        $string->validate()->willReturn(false);
        $bool->validate()->willReturn(true);

        $this->setData(false);

        $this->setStrictnessMode(StrictnessMode::ALL);
        $this->addConstraint($string);
        $this->addConstraint($bool);

        $this->validate()->shouldReturn(false);
    }

    function it_considers_validation_successful_if_any_constraint_passes_when_strictness_mode_is_any(
        ObjectConstraint $object, BooleanConstraint $bool, NumberConstraint $number
    )
    {
        $number->validate()->willReturn(true);
        $object->validate()->willReturn(false);
        $bool->validate()->willReturn(false);

        $this->setData(1);

        $this->setStrictnessMode(StrictnessMode::ANY);
        $this->addConstraint($number);
        $this->addConstraint($object);
        $this->addConstraint($bool);

        $this->validate()->shouldReturn(true);
    }

    function it_considers_validation_unsuccessful_if_all_constraints_fail_when_strictness_mode_is_any(
        ObjectConstraint $object, BooleanConstraint $bool, NumberConstraint $number
    )
    {
        $number->validate()->willReturn(false);
        $object->validate()->willReturn(false);
        $bool->validate()->willReturn(false);

        $this->setData('Foo');

        $this->setStrictnessMode(StrictnessMode::ANY);
        $this->addConstraint($number);
        $this->addConstraint($object);
        $this->addConstraint($bool);

        $this->validate()->shouldReturn(false);
    }
}

class TestableAbstractValidator extends AbstractValidator
{
    public function validate()
    {
        return $this->doValidate();
    }
}