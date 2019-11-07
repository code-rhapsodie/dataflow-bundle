<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use CodeRhapsodie\DataflowBundle\SchemaProvider\DataflowSchemaProvider;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;

class SchemaCommand extends Command
{
    protected static $defaultName = 'code-rhapsodie:dataflow:dump-schema';

    /** @var ConnectionFactory */
    private $connectionFactory;

    public function __construct(ConnectionFactory $connectionFactory)
    {
        parent::__construct();

        $this->connectionFactory = $connectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('List scheduled dataflows')
            ->setHelp('The <info>%command.name%</info> help you to generate SQL Query to create or update your database schema for this bundle')
            ->addOption('update', null, InputOption::VALUE_NONE, 'Dump only the update SQL queries.')
            ->addOption('connection', null, InputOption::VALUE_REQUIRED, 'Define the DBAL connection to use')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('connection') !== null) {
            $this->connectionFactory->setConnectionName($input->getOption('connection'));
        }

        $connection = $this->connectionFactory->getConnection();

        $schemaProvider = new DataflowSchemaProvider();
        $schema = $schemaProvider->createSchema();

        $sqls = $schema->toSql($connection->getDatabasePlatform());

        if ($input->getOption('update')) {
            $sm = $connection->getSchemaManager();

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
                $namespaces = $sm->listNamespaceNames();
            }

            $sequences = [];

            if ($connection->getDatabasePlatform()->supportsSequences()) {
                $sequences = $sm->listSequences();
            }

            $oldSchema = new Schema($tables, $sequences, $sm->createSchemaConfig(), $namespaces);

            $sqls = $schema->getMigrateFromSql($oldSchema, $connection->getDatabasePlatform());
        }
        $io = new SymfonyStyle($input, $output);
        $io->text('Execute theres SQL Query on your database:');
        foreach ($sqls as $sql) {
            $io->text($sql);
        }
    }
}
