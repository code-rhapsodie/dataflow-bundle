<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use CodeRhapsodie\DataflowBundle\SchemaProvider\DataflowSchemaProvider;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'code-rhapsodie:dataflow:database-schema', description: 'Generates schema create / update SQL queries', help: <<<'TXT'
The <info>%command.name%</info> help you to generate SQL Query to create or update your database schema for this bundle
TXT)]
final readonly class DatabaseSchemaCommand
{
    public function __construct(private ConnectionFactory $connectionFactory)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option('Dump only the update SQL queries.')] bool $dump = false,
        #[Option('Dump/execute only the update SQL queries.')] bool $update = false,
        #[Option('Define the DBAL connection to use')] ?string $connection = null,
    ): int {
        if ($connection !== null) {
            $this->connectionFactory->setConnectionName($connection);
        }

        $connection = $this->connectionFactory->getConnection();

        $schemaProvider = new DataflowSchemaProvider();
        $schema = $schemaProvider->createSchema();

        $sqls = $schema->toSql($connection->getDatabasePlatform());

        if ($update) {
            $sm = $connection->createSchemaManager();

            $tableArray = [JobRepository::TABLE_NAME, ScheduledDataflowRepository::TABLE_NAME];
            $tables = [];
            foreach ($sm->listTables() as $table) {
                /** @var Table $table */
                if (\in_array($table->getName(), $tableArray)) {
                    $tables[] = $table;
                }
            }

            $namespaces = [];

            if ($connection->getDatabasePlatform()->supportsSchemas()) {
                $namespaces = $sm->listSchemaNames();
            }

            $sequences = [];

            if ($connection->getDatabasePlatform()->supportsSequences()) {
                $sequences = $sm->listSequences();
            }

            $oldSchema = new Schema($tables, $sequences, $sm->createSchemaConfig(), $namespaces);

            $sqls = $connection->getDatabasePlatform()->getAlterSchemaSQL((new Comparator($connection->getDatabasePlatform()))->compareSchemas($oldSchema, $schema));

            if (empty($sqls)) {
                $io->info('There is no update SQL queries.');
            }
        }

        if ($dump) {
            $io->text('Execute these SQL Queries on your database:');
            foreach ($sqls as $sql) {
                $io->text($sql.';');
            }

            return Command::SUCCESS;
        }

        if (!$io->askQuestion(new ConfirmationQuestion('Are you sure to update database ?', true))) {
            $io->text('Execution canceled.');

            return Command::SUCCESS;
        }

        foreach ($sqls as $sql) {
            $connection->executeQuery($sql);
        }

        $io->success(\sprintf('%d queries executed.', \count($sqls)));

        return Command::SUCCESS;
    }
}
