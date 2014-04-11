<?php

namespace JsonSchema\Validator;

use JsonSchema\Enum\LogType;
use JsonSchema\Enum\StrictnessMode;
use JsonSchema\Validator\Constraint\ConstraintInterface;

class ConstraintGroup
{
    private $constraints = [];
    private $strictnessMode = StrictnessMode::ANY;
    private $allowedModes = [StrictnessMode::ANY, StrictnessMode::ALL];

    public function setStrictnessMode($mode)
    {
        if (!in_array($mode, $this->allowedModes)) {
            throw new \InvalidArgumentException(sprintf("%s is not a valid mode", $mode));
        }
        $this->strictnessMode = $mode;
    }

    public function getStrictnessMode()
    {
        return $this->strictnessMode;
    }

    public function addConstraint(ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;
    }

    public function getConstraints()
    {
        return $this->constraints;
    }

    public function setConstraints(array $constraints)
    {
        $this->constraints = $constraints;
    }

    public function validate()
    {
        if (empty($this->constraints)) {
            return true;
        }

        $successes = $failures = 0;

        foreach ($this->constraints as $constraint) {
            if ($this->strictnessMode === StrictnessMode::ANY) {
                $constraint->setLogType(LogType::INTERNAL);
            }

            if (true === $constraint->validate()) {
                $successes += 1;
            } else {
                $failures += 1;
            }
        }

        if ($this->strictnessMode === StrictnessMode::ANY) {
            $result = ($successes > 0) ? true : false;
        } else {
            $result = ($failures === 0) ? true : false;
        }

        // Re-add temporary errors that were stored internally to each constrain to main handler
        if (false === $result && $this->strictnessMode === StrictnessMode::ANY) {
            foreach ($this->constraints as $constraint) {
                $constraint->flushInternalErrors();
            }
        }

        return $result;
    }
}
