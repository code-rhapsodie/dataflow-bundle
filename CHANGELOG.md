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
