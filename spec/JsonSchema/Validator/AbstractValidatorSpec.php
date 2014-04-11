<?php

namespace spec\JsonSchema\Validator;

use JsonSchema\Enum\StrictnessMode;
use JsonSchema\Validator\AbstractValidator;
use JsonSchema\Validator\Constraint\ArrayConstraint;
use JsonSchema\Validator\Constraint\BooleanConstraint;
use JsonSchema\Validator\Constraint\ConstraintFactory;
use JsonSchema\Validator\Constraint\GenericConstraint;
use JsonSchema\Validator\Constraint\NumberConstraint;
use JsonSchema\Validator\Constraint\ObjectConstraint;
use JsonSchema\Validator\Constraint\StringConstraint;
use JsonSchema\Validator\ConstraintGroup;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class AbstractValidatorSpec extends ObjectBehavior
{
    use HasValidationChecker;

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

    function it_should_provide_easy_instantiation_of_constraint_classes()
    {
         $this->createConstraint('StringConstraint', 'foo', 'bar')->shouldReturnAnInstanceOf('JsonSchema\Validator\Constraint\ConstraintInterface');
    }

    function it_should_support_constraint_grouping(ObjectConstraint $constraint)
    {
        $this->setGroups([$constraint]);
        $this->getGroups()->shouldReturn([$constraint]);
    }

    function it_should_add_constraint_array_to_group(ObjectConstraint $object, ArrayConstraint $array)
    {
        $this->addConstraint([$object, $array]);
        $this->getGroups()->shouldHaveCount(1);
    }

    function it_should_add_constraint_object_to_group(ObjectConstraint $constraint)
    {
        $this->addConstraint($constraint);
        $this->getGroups()->shouldHaveCount(1);
    }

    function it_should_throw_exception_if_adding_invalid_data_type_for_constraint()
    {
        $this->shouldThrow('InvalidArgumentException')->duringAddConstraint(new \stdClass());
    }

    function it_should_validate_successfully_if_no_groups()
    {
        $this->validate()->shouldReturn(true);
    }

    function it_should_validate_by_validating_groups(ConstraintGroup $group)
    {
        $this->setGroups([$group]);
        $group->validate()->shouldBeCalled();

        $this->validate();
    }

    function it_should_support_array_constraint_creation(ConstraintFactory $factory, ArrayConstraint $constraint, EventDispatcher $dispatcher)
    {
        $factory->create('ArrayConstraint', 'foo', 'bar', $dispatcher)->willReturn($constraint);
        $this->setConstraintFactory($factory);
        $this->createArrayConstraint('foo', 'bar')->shouldReturnAnInstanceOf('JsonSchema\Validator\Constraint\ArrayConstraint');
    }

    function it_should_support_bool_constraint_creation(ConstraintFactory $factory, BooleanConstraint $constraint, EventDispatcher $dispatcher)
    {
        $factory->create('BooleanConstraint', 'foo', 'bar', $dispatcher)->willReturn($constraint);
        $this->setConstraintFactory($factory);
        $this->createBoolConstraint('foo', 'bar')->shouldReturnAnInstanceOf('JsonSchema\Validator\Constraint\BooleanConstraint');
    }

    function it_should_support_generic_constraint_creation(ConstraintFactory $factory, GenericConstraint $constraint, EventDispatcher $dispatcher)
    {
        $factory->create('GenericConstraint', 'foo', 'bar', $dispatcher)->willReturn($constraint);
        $this->setConstraintFactory($factory);
        $this->createGenericConstraint('foo', 'bar')->shouldReturnAnInstanceOf('JsonSchema\Validator\Constraint\GenericConstraint');
    }

    function it_should_support_number_constraint_creation(ConstraintFactory $factory, NumberConstraint $constraint, EventDispatcher $dispatcher)
    {
        $factory->create('NumberConstraint', 'foo', 'bar', $dispatcher)->willReturn($constraint);
        $this->setConstraintFactory($factory);
        $this->createNumberConstraint('foo', 'bar')->shouldReturnAnInstanceOf('JsonSchema\Validator\Constraint\NumberConstraint');
    }

    function it_should_support_object_constraint_creation(ConstraintFactory $factory, ObjectConstraint $constraint, EventDispatcher $dispatcher)
    {
        $factory->create('ObjectConstraint', 'foo', 'bar', $dispatcher)->willReturn($constraint);
        $this->setConstraintFactory($factory);
        $this->createObjectConstraint('foo', 'bar')->shouldReturnAnInstanceOf('JsonSchema\Validator\Constraint\ObjectConstraint');
    }

    function it_should_support_string_constraint_creation(ConstraintFactory $factory, StringConstraint $constraint, EventDispatcher $dispatcher)
    {
        $factory->create('StringConstraint', 'foo', 'bar', $dispatcher)->willReturn($constraint);
        $this->setConstraintFactory($factory);
        $this->createStringConstraint('foo', 'bar')->shouldReturnAnInstanceOf('JsonSchema\Validator\Constraint\StringConstraint');
    }
}

class TestableAbstractValidator extends AbstractValidator
{
    public function validate()
    {
        return $this->doValidate();
    }
}