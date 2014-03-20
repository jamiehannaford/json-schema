<?php

namespace JsonSchema;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait HasEventDispatcherTrait
{
    protected $eventDispatcher;

    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }
} 