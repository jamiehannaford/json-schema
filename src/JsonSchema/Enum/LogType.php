<?php

namespace JsonSchema\Enum;

abstract class LogType extends BaseEnum
{
    const DISABLED = 'disabled';
    const INTERNAL = 'internal';
    const EMITTING = 'emitting';
}