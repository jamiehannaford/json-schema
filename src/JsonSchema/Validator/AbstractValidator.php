<?php

namespace JsonSchema\Validator;

use JsonSchema\Enum\StrictnessMode;
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
    protected $handler;
    protected $constraintFactory;
    protected $constraints = [];

    protected $strictnessMode = StrictnessMode::ALL;

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

    public function setErrorHandler(ErrorHandlerInterface $errorHandler)
    {
        $this->handler = $errorHandler;
    }

    public function getErrorHandler()
    {
        return $this->handler;
    }

    public function setConstraintFactory(ConstraintFactoryInterface $factory)
    {
        $this->constraintFactory = $factory;
    }

    public function createConstraint($name, $value)
    {
        return $this->constraintFactory->create($name, $value, $this->eventDispatcher);
    }

    public function addConstraint(ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;
    }

    public function setConstraints(array $constraints)
    {
        $this->constraints = $constraints;
    }

    public function getConstraints()
    {
        return $this->constraints;
    }

    public function setStrictnessMode($mode)
    {
        $this->strictnessMode = $mode;
    }

    public function getStrictnessMode()
    {
        return $this->strictnessMode;
    }
}