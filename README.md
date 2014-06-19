json-schema
===========

This library allows you to validate data against JSON schemas. 

# Installation

```bash
$ composer require jamiehannaford/json-schema:dev-master
```

# Getting Started

When you validate instance data, the validator will iterate over your structure and check for each element's validity. If validation fails, an error is generated with details of the failure; how these errors are handled are completely up to you. The default strategy is buffering them; which means that they are collected by the handler in temporary storage until a time where you view them. You can write your own error handler and inject it in; the only condition is that it implements `JsonSchema\Validator\ErrorHandler\ErrorHandlerInterface`.

The responsibility of publishing these errors lies with the EventDispatcher which, for convenience, is Symfony's by default.

```php
use JsonSchema\Parser;
use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use JsonSchema\Validator\SchemaValidator;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();
$handler    = new BufferErrorHandler();
$dispatcher->addListener('validation.error', [$handler, 'receiveError']);
```

Once you've configured error handling, you're ready to initialize the validator:

```php
$validator = new SchemaValidator($dispatcher);
```

You inject your error dispatcher into the validator so that it knows how and who to notify when it runs into a validation failure. The validator, as you've probably guessed, is responsible to validating data structures. There's one final step: now you have your error dispatcher and validator, you tie everything together:

```php
$jsonSchema = '{"type": "object", "minProperties": 3}';

$parser = new Parser($jsonSchema);
$schema = $parser->parse($validator);
```

Cool, so we've set everything up and have represented the schema into its own object. This `$schema` object fully represents the `$jsonSchema` string and has the ability to validate any incoming data structures you want to validate. It also knows what to do when a validation failure occurs.

So, let's validate something:

```php
$data1   = (object) ['foo' => 'bar'];
$result1 = $schema->validateInstanceData($data1); // false

$data2   = 'foo';
$result2 = $schema->validateInstanceData($data2); // false

$data3   = (object) ['1' => 'foo', '2' => 'bar', '3' => 'baz'];
$result3 = $schema->validateInstanceData($data3); // true
```

Okay, two failures and one pass. But why did the failures happen? Let's find out:

```php
foreach ($handler->getErrors() as $error) {
    var_dump($error->getData());
}
```
