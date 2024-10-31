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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'code-rhapsodie:dataflow:database-schema', description: 'Generates schema create / update SQL queries')]
class DatabaseSchemaCommand extends Command
{
    public function __construct(private ConnectionFactory $connectionFactory)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setHelp('The <info>%command.name%</info> help you to generate SQL Query to create or update your database schema for this bundle')
            ->addOption('dump-sql', null, InputOption::VALUE_NONE, 'Dump only the update SQL queries.')
            ->addOption('update', null, InputOption::VALUE_NONE, 'Dump/execute only the update SQL queries.')
            ->addOption('no-interaction', null, InputOption::VALUE_NONE, 'Remove interactions')
            ->addOption('connection', null, InputOption::VALUE_REQUIRED, 'Define the DBAL connection to use');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (null !== $input->getOption('connection')) {
            $this->connectionFactory->setConnectionName($input->getOption('connection'));
        }

        $connection = $this->connectionFactory->getConnection();

        $schemaProvider = new DataflowSchemaProvider();
        $schema = $schemaProvider->createSchema();

        $sqls = $schema->toSql($connection->getDatabasePlatform());

        if ($input->getOption('update')) {
            $sm = $connection->createSchemaManager();

            $tableArray = [JobRepository::TABLE_NAME, ScheduledDataflowRepository::TABLE_NAME];
            $tables = [];
            foreach ($sm->listTables() as $table) {
                /** @var Table $table */
                if (in_array($table->getName(), $tableArray)) {
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
        }

        if ($input->getOption('dump-sql')) {
            $io->text('Execute these SQL Queries on your database:');
            foreach ($sqls as $sql) {
                $io->text($sql . ';');
            }

            return Command::SUCCESS;
        }

        if ($input->getOption('no-interation') === null && !$this->getHelper('question')->ask($input, $output, new ConfirmationQuestion('Are you sure to update database ?', false))) {
            $io->text("Execution canceled.");

            return Command::SUCCESS;
        }

        try {
            $connection->beginTransaction();
            foreach ($sqls as $sql) {
                $connection->executeQuery($sql);
            }
            $connection->commit();
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            $connection->rollBack();
            $io->text("Execution canceled. Rollback database");
            return Command::FAILURE;
        }

        return parent::SUCCESS;
    }
}
