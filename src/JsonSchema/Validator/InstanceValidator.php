<?php

namespace JsonSchema\Validator;

use JsonSchema\Schema\SchemaInterface;

class InstanceValidator extends AbstractValidator
{
    private $schema;

    public function setSchema(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    private function addKeywordConstraints($keywordName, $keywordValue)
    {

    }

    public function validate()
    {
        foreach ($this->schema as $keywordName => $keywordValue) {
            $this->addKeywordConstraints($keywordName, $keywordValue);
        }

        return $this->doValidate();
    }
}
