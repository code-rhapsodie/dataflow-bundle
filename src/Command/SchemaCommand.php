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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @codeCoverageIgnore
 * @deprecated This command is deprecated and will be removed in 6.0, use this command "code-rhapsodie:dataflow:database-schema" instead.
 */
#[AsCommand('code-rhapsodie:dataflow:dump-schema', 'Generates schema create / update SQL queries')]
class SchemaCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setHelp('The <info>%command.name%</info> help you to generate SQL Query to create or update your database schema for this bundle')
            ->addOption('update', null, InputOption::VALUE_NONE, 'Dump only the update SQL queries.')
            ->addOption('connection', null, InputOption::VALUE_REQUIRED, 'Define the DBAL connection to use')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->warning('This command is deprecated and will be removed in 6.0, use this command "code-rhapsodie:dataflow:database-schema" instead.');

        $options = array_filter($input->getOptions());

        //add -- before each keys
        $options = array_combine(
            array_map(fn($key) => '--' . $key, array_keys($options)),
            array_values($options)
        );

        $options['--dump-sql'] = true;

        $inputArray = new ArrayInput([
            'command' => 'code-rhapsodie:dataflow:database-schema',
            ...$options
        ]);

        return $this->getApplication()->doRun($inputArray, $output);
    }
}
