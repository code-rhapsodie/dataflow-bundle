# Code Rhapsodie Dataflow Bundle

DataflowBundle is a bundle for Symfony 3.4+ 
providing an easy way to create import / export dataflow.

[![Build Status](https://travis-ci.org/code-rhapsodie/dataflow-bundle.svg?branch=master)](https://travis-ci.org/code-rhapsodie/dataflow-bundle)

[![Coverage Status](https://coveralls.io/repos/github/code-rhapsodie/dataflow-bundle/badge.svg)](https://coveralls.io/github/code-rhapsodie/dataflow-bundle)

Dataflow uses a linear generic workflow in three parts:
 * one reader
 * any number of steps
 * one or more writers

The reader can read data from anywhere and return data row by row. Each step processes the current row data. 
The steps are executed in the order in which they are added.
And, one or more writers save the row anywhere you want.

As the following schema shows, you can define more than one dataflow:

![Dataflow schema](src/Resources/doc/schema.png)


# Features

* Define and configure a Dataflow
* Run the Job scheduled
* Run one Dataflow from the command line
* Define the schedule for a Dataflow from the command line
* Enable/Disable a scheduled Dataflow from the command line
* Display the list of scheduled Dataflow from the command line
* Display the result for the last Job for a Dataflow from the command line
* Work with multiple Doctrine DBAL connections


## Installation

### Add the dependency

To install this bundle, run this command :

```shell script
$ composer require code-rhapsodie/dataflow-bundle
```

#### Suggest

You can use the generic readers, writers and steps from [PortPHP](https://github.com/portphp/portphp).

For the writers, you must use the adapter `CodeRhapsodie\DataflowBundle\DataflowType\Writer\PortWriterAdapter` like this:

```php
<?php
// ...
$streamWriter = new \Port\Writer\StreamMergeWriter();

$builder->addWriter(new \CodeRhapsodie\DataflowBundle\DataflowType\Writer\PortWriterAdapter($streamWriter));
// ...
```

### Register the bundle

#### Symfony 4 (new tree)

For Symfony 4, add `CodeRhapsodie\DataflowBundle\CodeRhapsodieDataflowBundle::class => ['all' => true],
` in the `config/bundles.php` file.

Like this:

```php
<?php

return [
     // ...
    CodeRhapsodie\DataflowBundle\CodeRhapsodieDataflowBundle::class => ['all' => true],
    // ...
];
```

#### Symfony 3.4 (old tree)

For Symfony 3.4, add a new line in the `app/AppKernel.php` file.

Like this:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new CodeRhapsodie\DataflowBundle\CodeRhapsodieDataflowBundle(),
        // ...
    ];
}
```

### Update the database

This bundle uses Doctrine DBAL to store Dataflow schedule into the database table (`cr_dataflow_scheduled`)
and jobs (`cr_dataflow_job`).

If you use [Doctrine Migration Bundle](https://symfony.com/doc/master/bundles/DoctrineMigrationsBundle/index.html) or [Phinx](https://phinx.org/) 
or [Kaliop Migration Bundle](https://github.com/kaliop-uk/ezmigrationbundle) or whatever,
you can add a new migration with the generated SQL query from this command:

```shell script
$ bin/console code-rhapsodie:dataflow:dump-schema
```

If you have already the tables, you can add a new migration with the generated update SQL query from this command:

```shell script
$ bin/console code-rhapsodie:dataflow:dump-schema --update
```

## Configuration

By default, the Doctrine DBAL connection used is `default`. You can configure the default connection.
Add this configuration into your Symfony configuration:

```yaml
code_rhapsodie_dataflow:
  dbal_default_connection: test #Name of the default connection used by Dataflow bundle
```

## Define a dataflow type

This bundle uses a fixed and simple workflow structure in order to let you focus on the data processing logic part of your dataflow.

A dataflow type defines the different parts of your dataflow. A dataflow is made of:
- exactly one *Reader*
- any number of *Steps*
- one or more *Writers*

Dataflow types can be configured with options.

A dataflow type must implement `CodeRhapsodie\DataflowBundle\DataflowType\DataflowTypeInterface`.

To help with creating your dataflow types, an abstract class `CodeRhapsodie\DataflowBundle\DataflowType\AbstractDataflowType`
is provided, allowing you to define your dataflow through a handy builder `CodeRhapsodie\DataflowBundle\DataflowType\DataflowBuilder`.

This is an example to define one class DataflowType:

```php
<?php
namespace CodeRhapsodie\DataflowExemple\DataflowType;

use CodeRhapsodie\DataflowBundle\DataflowType\AbstractDataflowType;
use CodeRhapsodie\DataflowBundle\DataflowType\DataflowBuilder;
use CodeRhapsodie\DataflowExemple\Reader\FileReader;
use CodeRhapsodie\DataflowExemple\Writer\FileWriter;

class MyFirstDataflowType extends AbstractDataflowType
{
    private $myReader;

    private $myWriter;

    public function __construct(FileReader $myReader, FileWriter $myWriter)
    {
        $this->myReader = $myReader;
        $this->myWriter = $myWriter;
    }

    protected function buildDataflow(DataflowBuilder $builder, array $options): void
    {
        $this->myWriter->setDestinationFilePath($options['to-file']);

        $builder
            ->setReader($this->myReader->read($options['from-file']))
            ->addStep(function ($data) use ($options) {
                // TODO : Write your code here...
                return $data;
            })
            ->addWriter($this->myWriter)
        ;
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(['to-file' => '/tmp/dataflow.csv', 'from-file' => null]);
        $optionsResolver->setRequired('from-file');
    }

    public function getLabel(): string
    {
        return 'My First Dataflow';
    }

    public function getAliases(): iterable
    {
        return ['mfd'];
    }
}

```

Dataflow types must be tagged with `coderhapsodie.dataflow.type`.

If you're using Symfony auto-configuration for your services, this tag will be automatically added to all services implementing `DataflowTypeInterface`.

Otherwise, manually add the tag `coderhapsodie.dataflow.type` in your dataflow type service configuration:

```yaml
    CodeRhapsodie\DataflowExemple\DataflowType\MyFirstDataflowType:
      tags:
        - { name: coderhapsodie.dataflow.type }
```

### Use options for your dataflow type

The `AbstractDataflowType` can help you define options for your Dataflow type.

Add this method in your DataflowType class:

```php
<?php
// ...
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyFirstDataflowType extends AbstractDataflowType
{
    // ...
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(['to-file' => '/tmp/dataflow.csv', 'from-file' => null]);
        $optionsResolver->setRequired('from-file');
    }

}
```

With this configuration, the option `fileName` is required. For an advanced usage of the option resolver, read the [Symfony documentation](https://symfony.com/doc/current/components/options_resolver.html).

### Check if your DataflowType is ready

Execute this command to check if your DataflowType is correctly registered:

```shell script
$ bin/console debug:container --tag coderhapsodie.dataflow.type --show-private
```

The result is like this:

```
Symfony Container Public and Private Services Tagged with "coderhapsodie.dataflow.type" Tag
===========================================================================================

 ---------------------------------------------------------------- ---------------------------------------------------------------- 
  Service ID                                                       Class name                                                      
 ---------------------------------------------------------------- ---------------------------------------------------------------- 
  CodeRhapsodie\DataflowExemple\DataflowType\MyFirstDataflowType   CodeRhapsodie\DataflowExemple\DataflowType\MyFirstDataflowType  
 ---------------------------------------------------------------- ---------------------------------------------------------------- 

```


### Readers

*Readers* provide the dataflow with elements to import / export. Usually, elements are read from an external resource (file, database, webservice, etc).

A *Reader* can be any `iterable`.

The only constraint on the returned elements typing is that they cannot be `false`.

The reader can be a generator like this example :

```php
<?php

namespace CodeRhapsodie\DataflowExemple\Reader;

class FileReader
{
    public function read(string $filename): iterable
    {
        if (!$filename) {
            throw new \Exception("The file name is not defined. Define it with 'setFilename' method");
        }

        if (!$fh = fopen($filename, 'r')) {
            throw new \Exception("Unable to open file '".$filename."' for read.");
        }

        while (false !== ($read = fgets($fh))) {
            yield explode('|', trim($read));
        }
    }
}
```

You can set up this reader as follows:

```php
$builder->setReader(($this->myReader)())
``` 


### Steps

*Steps* are operations performed on the elements before they are handled by the *Writers*. Usually, steps are either:
- converters, that alter the element
- filters, that conditionally prevent further operations on the element

A *Step* can be any callable, taking the element as its argument, and returning either:
- the element, possibly altered
- `false`, if no further operations should be performed on this element

A few examples:

```php
<?php
//[...]
$builder->addStep(function ($item) {
    // Titles are changed to all caps before export
    $item['title'] = strtoupper($item['title']);

    return $item;
});

$builder->addStep(function ($item) {
    // Private items are not exported
    if ($item['private']) {
        return false;
    }

    return $item;
});
//[...]
```

### Writers

*Writers* perform the actual import / export operations.

A *Writer* must implement `CodeRhapsodie\DataflowBundle\DataflowType\Writer\WriterInterface`.
As this interface is not compatible with `Port\Writer`, the adapter `CodeRhapsodie\DataflowBundle\DataflowType\Writer\PortWriterAdapter` is provided.

This example show how to use the predefined PhpPort Writer :

```php
$builder->addWriter(new PortWriterAdapter(new \Port\FileWriter()));
```

Or your own Writer:

```php
<?php
namespace CodeRhapsodie\DataflowExemple\Writer;

use CodeRhapsodie\DataFlowBundle\DataflowType\Writer\WriterInterface;

class FileWriter implements WriterInterface
{
    private $fh;

    /** @var string */
    private $path;

    public function setDestinationFilePath(string $path) {
        $this->path = $path;
    }

    public function prepare()
    {
        if (null === $this->path) {
            throw new \Exception('Define the destination file name before use');
        }
        if (!$this->fh = fopen($this->path, 'w')) {
            throw new \Exception('Unable to open in write mode the output file.');
        }
    }

    public function write($item)
    {
        fputcsv($this->fh, $item);
    }

    public function finish()
    {
        fclose($this->fh);
    }
}
```

#### CollectionWriter

If you want to write multiple items from a single item read, you can use the generic `CollectionWriter`. This writer will iterate over any `iterable` it receives, and pass each item from that collection to your own writer that handles single items.

```php
$builder->addWriter(new CollectionWriter($mySingleItemWriter));
```

#### DelegatorWriter

If you want to call different writers depending on what item is read, you can use the generic `DelegatorWriter`.

As an example, let's suppose our items are arrays with the first entry being either `product` or `order`. We want to use a different writer based on that value.

First, create your writers implementing `DelegateWriterInterface` (this interface extends `WriterInterface` so your writers can still be used without the `DelegatorWriter`).

```php
<?php
namespace CodeRhapsodie\DataflowExemple\Writer;

use CodeRhapsodie\DataFlowBundle\DataflowType\Writer\WriterInterface;

class ProductWriter implements DelegateWriterInterface
{
    public function supports($item): bool
    {
        return 'product' === reset($item);
    }

    public function prepare()
    {
    }

    public function write($item)
    {
        // Process your product
    }

    public function finish()
    {
    }
}
```

```php
<?php
namespace CodeRhapsodie\DataflowExemple\Writer;

use CodeRhapsodie\DataFlowBundle\DataflowType\Writer\WriterInterface;

class OrderWriter implements DelegateWriterInterface
{
    public function supports($item): bool
    {
        return 'order' === reset($item);
    }

    public function prepare()
    {
    }

    public function write($item)
    {
        // Process your order
    }

    public function finish()
    {
    }
}
```

Then, configure your `DelegatorWriter` and add it to your dataflow type.

```php
    protected function buildDataflow(DataflowBuilder $builder, array $options): void
    {
        // Snip add reader and steps

        $delegatorWriter = new DelegatorWriter();
        $delegatorWriter->addDelegate(new ProductWriter());
        $delegatorWriter->addDelegate(new OrderWriter());

        $builder->addWriter($delegatorWriter);
    }
```

During execution, the `DelegatorWriter` will simply pass each item received to its first delegate (in the order those were added) that supports it. If no delegate supports an item, an exception will be thrown.

## Queue

All pending dataflow job processes are stored in a queue into the database.

Add this command into your crontab for execute all queued jobs:

```shell script
$ SYMFONY_ENV=prod php bin/console code-rhapsodie:dataflow:run-pending
```

## Commands

Several commands are provided to manage schedules and run jobs.

`code-rhapsodie:dataflow:run-pending` Executes job in the queue according to their schedule.

`code-rhapsodie:dataflow:schedule:list` Display the list of dataflows scheduled.

`code-rhapsodie:dataflow:schedule:change-status` Enable or disable a scheduled dataflow

`code-rhapsodie:dataflow:schedule:add` Add the schedule for a dataflow.

`code-rhapsodie:dataflow:job:show` Display the last result of a job.

`code-rhapsodie:dataflow:execute` Let you execute one dataflow job.

`code-rhapsodie:dataflow:dump-schema` Generates schema create / update SQL queries

### Work with many databases

All commands have a `--connection` option to define what Doctrine DBAL connection to use during execution.

Example:

This command uses the `default` DBAL connection to generate all schema update queries.

```shell script
$ bin/console code-rhapsodie:dataflow:dump-schema --update --connection=default
```

To execute all pending job for a specific connection use:

```shell script
# Run for dataflow DBAL connection
$ bin/console code-rhapsodie:dataflow:run-pending --connection=dataflow
# Run for default DBAL connection
$ bin/console code-rhapsodie:dataflow:run-pending --connection=default
```

# Issues and feature requests

Please report issues and request features at https://github.com/code-rhapsodie/dataflow-bundle/issues.

# Contributing

Contributions are very welcome. Please see [CONTRIBUTING.md](CONTRIBUTING.md) for
details. Thanks to [everyone who has contributed](https://github.com/code-rhapsodie/dataflow-bundle/graphs/contributors)
already.

# License

This package is licensed under the [MIT license](LICENSE).
