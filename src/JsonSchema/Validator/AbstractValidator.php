<?php

namespace JsonSchema\Validator;

use JsonSchema\HasEventDispatcherTrait;
use JsonSchema\Validator\Constraint\ConstraintFactory;
use JsonSchema\Validator\Constraint\ConstraintFactoryInterface;
use JsonSchema\Validator\Constraint\ConstraintInterface;
use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use JsonSchema\Validator\ErrorHandler\ErrorHandlerInterface;
use JsonSchema\Validator\ErrorHandler\HasErrorHandlerTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractValidator implements ValidatorInterface
{
    use HasEventDispatcherTrait, HasErrorHandlerTrait;

    protected $data;
    protected $constraintFactory;
    protected $groups = [];

    public function __construct(
        EventDispatcherInterface $errorDispatcher = null,
        ConstraintFactoryInterface $constraintFactory = null
    ) {
        $this->setEventDispatcher($errorDispatcher ?: $this->getDefaultErrorDispatcher());
        $this->setConstraintFactory($constraintFactory ?: new ConstraintFactory());
    }

    protected function getDefaultErrorDispatcher()
    {
        $dispatcher = new EventDispatcher();
        $handler = new BufferErrorHandler();
        //$dispatcher->addSubscriber($handler);
        $dispatcher->addListener('validation.error', [$handler, 'receiveError']);

        return $dispatcher;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setConstraintFactory(ConstraintFactoryInterface $factory)
    {
        $this->constraintFactory = $factory;
    }

    public function createConstraint($name, $value)
    {
        return $this->constraintFactory->create($name, $value, $this->eventDispatcher);
    }

    public function addConstraint($value, $strictnessMode = null)
    {
        $group = new ConstraintGroup($strictnessMode);

        if (is_array($value)) {
            foreach ($value as $constraint) {
                $group->addConstraint($constraint);
            }
        } elseif ($value instanceof ConstraintInterface) {
            $group->addConstraint($value);
        } else {
            throw new \InvalidArgumentException(
                "An array or single instance of ConstraintInterface must be provided"
            );
        }

        $this->addGroup($group);
    }

    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function addGroup(ConstraintGroup $group)
    {
        $this->groups[] = $group;
    }

    protected function doValidate()
    {
        if (empty($this->groups)) {
            return true;
        }

        foreach ($this->groups as $group) {
            if (true !== $group->validate()) {
                return false;
            }
        }

        return true;
    }
}