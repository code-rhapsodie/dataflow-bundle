<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\SchemaProvider;

use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class JobSchemaProvider.
 *
 * @codeCoverageIgnore
 */
class DataflowSchemaProvider
{
    public function createSchema()
    {
        $schema = new Schema();
        $tableJob = $schema->createTable(JobRepository::TABLE_NAME);
        $tableJob->addColumn('id', 'integer', [
            'autoincrement' => true,
        ]);
        $tableJob->setPrimaryKey(['id']);

        $tableJob->addColumn('scheduled_dataflow_id', 'integer', ['notnull' => false]);
        $tableJob->addColumn('status', 'integer', ['notnull' => true]);
        $tableJob->addColumn('label', 'string', ['notnull' => true, 'length' => 255]);
        $tableJob->addColumn('dataflow_type', 'string', ['notnull' => true, 'length' => 255]);
        $tableJob->addColumn('options', 'json', ['notnull' => true]);
        $tableJob->addColumn('requested_date', 'datetime', ['notnull' => false]);
        $tableJob->addColumn('count', 'integer', ['notnull' => false]);
        $tableJob->addColumn('exceptions', 'json', ['notnull' => false]);
        $tableJob->addColumn('start_time', 'datetime', ['notnull' => false]);
        $tableJob->addColumn('end_time', 'datetime', ['notnull' => false]);

        $tableSchedule = $schema->createTable(ScheduledDataflowRepository::TABLE_NAME);
        $tableSchedule->addColumn('id', 'integer', [
            'autoincrement' => true,
        ]);
        $tableSchedule->setPrimaryKey(['id']);
        $tableSchedule->addColumn('label', 'string', ['notnull' => true, 'length' => 255]);
        $tableSchedule->addColumn('dataflow_type', 'string', ['notnull' => true, 'length' => 255]);
        $tableSchedule->addColumn('options', 'json', ['notnull' => true]);
        $tableSchedule->addColumn('frequency', 'string', ['notnull' => true, 'length' => 255]);
        $tableSchedule->addColumn('next', 'datetime', ['notnull' => false]);
        $tableSchedule->addColumn('enabled', 'boolean', ['notnull' => true]);

        $tableJob->addForeignKeyConstraint($tableSchedule, ['scheduled_dataflow_id'], ['id']);
        $tableJob->addIndex(['status'], 'idx_status');

        return $schema;
    }
}
