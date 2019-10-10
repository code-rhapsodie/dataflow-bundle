# Code-Rhapsodie Dataflow Bundle

DataflowBundle is a bundle for Symfony 3.4+ 
providing an easy way to create import / export dataflow.

[![Build Status](https://travis-ci.org/code-rhapsodie/dataflow-bundle.svg?branch=master)](https://travis-ci.org/code-rhapsodie/dataflow-bundle)

[![Coverage Status](https://coveralls.io/repos/github/code-rhapsodie/dataflow-bundle/badge.svg)](https://coveralls.io/github/code-rhapsodie/dataflow-bundle)

# Features

* Define and configure a Dataflow
* Run the Job scheduled
* Run one Dataflow from the command line
* Define the schedule for a Dataflow from the command line
* Enable/Disable a scheduled Dataflow from the command line
* Display the list of scheduled Dataflow from the command line
* Display the result for the last Job for a Dataflow from the command line


## Installation

### Add the dependency

To install this bundle, run this command :

```shell script
$ composer require code-rhapsodie/dataflow
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

This bundle uses Doctrine ORM for drive the database table for store Dataflow schedule (`cr_dataflow_scheduled`)
and jobs (`cr_dataflow_job`).

#### Doctrine migration

Execute the command to generate the migration for your database:

```shell script
$ bin/console doctrine:migration:diff
```

#### Other migration tools

If you use [Phinx](https://phinx.org/) or [Kaliop Migration Bundle](https://github.com/kaliop-uk/ezmigrationbundle) or whatever,
you can add a new migration with the generated SQL query from this command:

```shell script
$ bin/console doctrine:schema:update --dump-sql
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
        
        $this->myReader->setFilename($options['fileName']);

        $builder->setReader($this->myReader)
            ->addStep(function($data) use ($options) {
                // TODO : Write your code here...
                return $data;
            })
            ->addWriter($this->myWriter)
        ;
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'my_option' => 'my_default_value',
            'fileName'  => null,
        ]);
        $optionsResolver->setRequired('fileName');
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
        $optionsResolver->setDefaults([
            'my_option' => 'my_default_value',
            'fileName'  => null,
        ]);
        $optionsResolver->setRequired('fileName');
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
    private $filename;

    /**
     * Set the filename option needed by the Reader.
     */
    public function setFilename(string $filename) {
        $this->filename = $filename;
    }

    public function __invoke(): iterable
    {
        if (!$this->filename) {
            throw new \Exception("The file name is not defined. Define it with 'setFilename' method");
        }

        if (!$fh = fopen($this->filename, 'r')) {
            throw new \Exception("Unable to open file '".$this->filename."' for read.");
        }

        while (false === ($read = fread($fh, 1024))) {
            yield explode("|", $read);
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
- filters, that conditionally prevents further operations on the element

A *Step* can be any callable, taking the element as its argument, and returning either:
- the element, possibly altered
- `false`, if no further operations should be performed on this element

A few examples:

```php
$builder->addStep(function($item) {
    // Titles are changed to all caps before export
    $item['title'] = strtoupper($item['title']);

    return $item;
});

$builder->addStep(function($item) {
    // Private items are not exported
    if ($item['private']) {
        return false;
    }

    return $item;
});
```

### Writers

*Writers* performs the actual import / export operations.

A *Writer* must implements `CodeRhapsodie\DataflowBundle\DataflowType\Writer\WriterInterface`.
As this interface is not compatible with `Port\Writer`, the adapter `CodeRhapsodie\DataflowBundle\DataflowType\Writer\PortWriterAdapter` is provided.

This example show how to use the predefined PhpPort Writer :

```php
$builder->addWriter(new PortWriterAdapter(new \Port\FileWriter()));
```

Or you own Writer:

```php
<?php
namespace CodeRhapsodie\DataflowExemple\Writer;

use CodeRhapsodie\DataFlowBundle\DataflowType\Writer\WriterInterface;

class FileWriter implements WriterInterface
{
    private $fh;

    public function prepare()
    {

        if (!$this->fh = fopen('/path/to/file', 'w')) {
            throw new \Exception("Unable to open in write mode the output file.");
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

## Queue

All pending dataflow job processes are stored in a queue into the database.

Add this command into your crontab for execute all queued job:

```shell script
$ SYMFONY_ENV=prod php bin/console code-rhapsodie:dataflow:job:run-pending
```

## Commands

Several commands are provided to manage schedules and run jobs.

`code-rhapsodie:dataflow:job:run-pending` Executes job in the queue according to their schedule.

`code-rhapsodie:dataflow:schedule:list` Display the list of dataflows scheduled.

`code-rhapsodie:dataflow:schedule:change-status` Enable or disable a scheduled dataflow

`code-rhapsodie:dataflow:schedule:add` Add the schedule for a dataflow.

`code-rhapsodie:dataflow:job:show` Display the last result of a job.

`code-rhapsodie:dataflow:execute` Lets you execute one dataflow job.


# Issues and feature requests

Please report issues and request features at https://github.com/code-rhapsodie/dataflow-bundle/issues.

# Contributing

Contributions are very welcome. Please see [CONTRIBUTING.md](CONTRIBUTING.md) for
details. Thanks to [everyone who has contributed](https://github.com/code-rhapsodie/dataflow-bundle/graphs/contributors)
already.

# License

This package is licensed under the [MIT license](LICENSE).
