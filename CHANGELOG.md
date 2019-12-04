# Version 2.1.0

* Added CollectionWriter and DelegatorWriter
* Adding Symfony 5.0 compatibility
* Save all exceptions caught in the log for `code-rhapsodie:dataflow:execute`

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
