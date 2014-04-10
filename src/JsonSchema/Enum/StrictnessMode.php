<?php

namespace JsonSchema\Enum;

abstract class StrictnessMode extends BaseEnum
{
    const ALL  = 'and';
    const ANY  = 'any';
    const SOME = 'some';
}