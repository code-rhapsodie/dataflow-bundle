# Version 5.2.0
* Added custom index for job status

# Version 5.1.0
* Refactor SchemaDump command

# Version 5.0.1
* Fix compatibility with doctrine 4

# Version 5.0.0
* Initiate Kudos on dataflow-bundle
* Added Symfony 7 support
* Removed Symfony 6 compatibility
* Removed Symfony 5 compatibility
* Removed Symfony 4 compatibility
* Removed Symfony 3 compatibility
* Changed README.md
* Added CI

# Version 4.1.3
* Fix log exception argument typing

# Version 4.1.2
* Fix DBAL 2.12 compatibility break

# Version 4.1.0

* Added custom index for exception log

# Version 4.0.0

* Added Symfony 6 support
* PHP minimum requirements bumped to 8.0

# Version 3.1.0

* Added optional "messenger mode", to delegate jobs execution to workers from the Symfony messenger component
* Added support for asynchronous steps execution, using the AMPHP library (contribution from [matyo91](https://github.com/matyo91))

# Version 3.0.0

* Added PHP 8 support
* PHP minimum requirements bumped to 7.3
* Added Doctrine DBAL 3 support
* Doctrine DBAL minimum requirements bumped to 2.12

# Version 2.2.0

* Improve logging Dataflow job

# Version 2.1.1

* Fixed some Symfony 5 compatibility issues

# Version 2.1.0

* Added CollectionWriter and DelegatorWriter
* Adding Symfony 5.0 compatibility
* Save all exceptions caught in the log for `code-rhapsodie:dataflow:execute`
* Added more output when errors occured during `code-rhapsodie:dataflow:execute`

# Version 2.0.2

* Fixed the connection proxy class created by the factory

# Version 2.0.1

* Fixed next execution time not increasing for scheduled dataflows

# Version 2.0.0

* Add Doctrine DBAL multi-connection support
* Add configuration to define the default Doctrine DBAL connection
* Remove Doctrine ORM
* Rewrite repositories

# Version 1.0.1

* Fix lost dependency
* Fix schedule removing

# Version 1.0.0

Initial version

* Define and configure a Dataflow
* Run the Job scheduled
* Run one Dataflow from the command line
* Define the schedule for a Dataflow from the command line
* Enable/Disable a scheduled Dataflow from the command line
* Display the list of scheduled Dataflow from the command line
* Display the result for the last Job for a Dataflow from the command line
