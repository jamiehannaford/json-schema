<?php

namespace spec\JsonSchema\Validator;

use JsonSchema\Validator\FailureEvent;
use Prophecy\Argument;
use JsonSchema\Validator\Constraint\ConstraintFactoryInterface;
use JsonSchema\Validator\Constraint\ConstraintInterface;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Prophet;

trait HasValidationChecker
{
    private $prophet;

    function let()
    {
        $this->prophet = new Prophet();
    }

    function letgo()
    {
        if ($this->prophet) {
            $this->prophet->checkPredictions();
        }
    }

    protected function getCollaboratorName(Collaborator $object)
    {
        $reflection = new \ReflectionObject($object->reveal());
        $namespace = $reflection->getNamespaceName();
        return substr($namespace, strrpos($namespace, '\\') + 1);
    }

    protected function prophesizeConstraint($name)
    {
        $constraint = $this->prophet->prophesize('JsonSchema\\Validator\\Constraint\\' . $name);
        $constraint->willImplement('JsonSchema\Validator\Constraint\ConstraintInterface');
        return $constraint;
    }

    protected function addConstraintPromises(ConstraintFactoryInterface $factory, ConstraintInterface $constraint)
    {
        $constraintName = $this->getCollaboratorName($constraint);
        $factory->create($constraintName, Argument::any(), Argument::any())->shouldBeCalled();
        $factory->create($constraintName, Argument::any(), Argument::any())->willReturn($constraint);
    }

    protected function testValidationPrediction($name, $constraint, $value = 'Foo')
    {
        $factory = $this->prophet->prophesize('JsonSchema\Validator\Constraint\ConstraintFactory');

        if (is_array($constraint)) {
            foreach ($constraint as $_constraint) {
                $this->addConstraintPromises($factory, $_constraint);
            }
        } else {
            $this->addConstraintPromises($factory, $constraint);
        }

        $this->setConstraintFactory($factory);
        $this->addKeywordConstraints($name, $value);
    }

    protected function testFailureDispatch($value, $message, $expected = null)
    {
        $event = new FailureEvent([
            'value' => $value,
            'message' => $message,
            'expected' => $expected
        ]);

        if (!$this->prophet) {
            $this->prophet = new Prophet();
        }

        $dispatcher = $this->prophet->prophesize('Symfony\Component\EventDispatcher\EventDispatcher');
        $dispatcher->dispatch('validation.error', $event)->shouldBeCalled();

        $this->setEventDispatcher($dispatcher);
    }
} 