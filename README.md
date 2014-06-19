json-schema
===========

This library allows you to validate any form of data against JSON schemas of your choosing. The data can be in any form native to PHP, and the schemas are defined through string representations. Error handling is abstracted out, allowing you the power to define your own error handling logic.

# Installation

As you've probably expected, you will need to install this package through ComposeR:

```bash
$ composer require jamiehannaford/json-schema:dev-master
```

This will download the source code for the project and any dependencies it relies on. It will also provide you with an autoloader to use:

```php
require 'vendor/autoload.php';
```

# Getting Started

When you validate instance data, the validator will iterate over your structure and check for each element's validity. If validation fails, an error is generated with details of the failure; how these errors are handled is completely up to you. The default strategy is to buffer them; which means that they are collected by the handler in temporary storage until a time where you need to access them. You can also write your own error handler class and inject it in; the only condition is that it implements [`JsonSchema\Validator\ErrorHandler\ErrorHandlerInterface`](/src/JsonSchema/Validator/ErrorHandler/ErrorHandlerInterface.php).

The responsibility of publishing these errors lies with the EventDispatcher. We use Symfony's by default. So to begin, you need to set up your error handler and attach it as a subscriber to your dispatcher:

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

You inject your error dispatcher into the validator so that it knows how to react when it runs into a validation failure. If you can't be bothered with all this, default implementations will be used (Symfony's dispatcher, the buffering error handler). 

The validator, as you've probably guessed, is responsible to validating data structures. There's one final step: now you have your error dispatcher and validator, you tie everything together:

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
