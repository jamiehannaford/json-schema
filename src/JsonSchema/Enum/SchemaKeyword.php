<?php

namespace JsonSchema\Enum;

abstract class SchemaKeyword extends BaseEnum
{
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const MULTIPLE_OF = 'multipleOf';
    const MAXIMUM = 'maximum';
    const EXCLUSIVE_MAXIMUM = 'exclusiveMaximum';
    const MINIMUM = 'minimum';
    const EXCLUSIVE_MINIMUM = 'exclusiveMinimum';
    const MIN_LENGTH = 'minLength';
    const MAX_LENGTH = 'maxLength';
    const PATTERN = 'pattern';
    const ADDITIONAL_ITEMS = 'additionalItems';
    const ITEMS = 'items';
    const MAX_ITEMS = 'maxItems';
    const MIN_ITEMS = 'minItems';
    const UNIQUE_ITEMS = 'uniqueItems';
    const MAX_PROPERTIES = 'maxProperties';
    const MIN_PROPERTIES = 'minProperties';
    const REQUIRED = 'required';
    const ADDITIONAL_PROPERTIES = 'additionalProperties';
    const PROPERTIES = 'properties';
    const PATTERN_PROPERTIES = 'patternProperties';
    const DEPENDENCIES = 'dependencies';
    const ENUM = 'enum';
    const TYPE = 'type';
    const NOT = 'not';
    const ALL_OF = 'allOf';
    const ANY_OF = 'anyOf';
    const ONE_OF = 'oneOf';
    const DEFINITIONS = 'definitions';
    const FORMS = 'format';
}