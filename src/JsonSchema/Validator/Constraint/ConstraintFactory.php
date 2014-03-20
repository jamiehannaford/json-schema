<?php

namespace JsonSchema\Validator\Constraint;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConstraintFactory
{
    public function create($class, $value, EventSubscriberInterface $handler)
    {
        $class = $this->normalizeClassName($class);

        if (!class_exists($class)) {
            throw new \RuntimeException(sprintf("%s class does not exist", $class));
        }

        $constraint = new $class($value, $handler);

        if (!$constraint instanceof ConstraintInterface) {
            throw new \InvalidArgumentException(sprintf(
                "%s does not implement %s\\ConstraintInterface",
                $class, __NAMESPACE__
            ));
        }

        return $constraint;
    }

    protected function normalizeClassName($class)
    {
        return (strpos($class, '\\') !== false)
            ? $class
            : sprintf("%s\\%s", __NAMESPACE__, $class);
    }
}