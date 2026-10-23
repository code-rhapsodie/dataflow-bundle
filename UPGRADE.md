# Upgrade to v6.x

[BC] Dates are now stored in UTC (`cr_dataflow_job`: `requested_date`, `start_time`, `end_time`; `cr_dataflow_scheduled`: `next`),
and read as UTC, whatever the PHP default timezone is.
Dates previously stored in the PHP default timezone must be converted to UTC (make a backup first, the command is not idempotent):

```
bin/console code-rhapsodie:dataflow:convert-dates-to-utc --next --requested-date [--timezone=Europe/Paris] [--dry-run]
```

`start_time` and `end_time` are always converted. `--next` and `--requested-date` are optional: those dates may have been
created by another tool that does not store them in the PHP timezone.

## If you use ibexa-dataflow-bundle

The forms of [ibexa-dataflow-bundle](https://github.com/code-rhapsodie/ibexa-dataflow-bundle) stored the `next` and `requested_date` dates
in a way that depends on both the PHP timezone and the timezone of the user who filled the form.
These dates cannot be converted with the command above: follow the
[ibexa-dataflow-bundle upgrade guide](https://github.com/code-rhapsodie/ibexa-dataflow-bundle/blob/master/UPGRADE.md) instead.

# Upgrade from v1.x to v2.0

[BC] `JobRepository` and `ScheduledDataflowRepository` are no longer a Doctrine ORM repository.
