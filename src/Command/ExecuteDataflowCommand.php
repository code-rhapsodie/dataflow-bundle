<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Runs one dataflow.
 *
 * @codeCoverageIgnore
 */
#[AsCommand('code-rhapsodie:dataflow:execute', 'Runs one dataflow type with provided options')]
class ExecuteDataflowCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private DataflowTypeRegistryInterface $registry, private ConnectionFactory $connectionFactory)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command runs one dataflow with the provided options.

  <info>php %command.full_name% App\Dataflow\MyDataflow '{"option1": "value1", "option2": "value2"}'</info>
EOF
            )
            ->addArgument('fqcn', InputArgument::REQUIRED, 'FQCN or alias of the dataflow type')
            ->addArgument('options', InputArgument::OPTIONAL, 'Options for the dataflow type as a json string', '[]')
            ->addOption('connection', null, InputOption::VALUE_REQUIRED, 'Define the DBAL connection to use');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null !== $input->getOption('connection')) {
            $this->connectionFactory->setConnectionName($input->getOption('connection'));
        }
        $fqcnOrAlias = $input->getArgument('fqcn');
        $options = json_decode($input->getArgument('options'), true, 512, JSON_THROW_ON_ERROR);
        $io = new SymfonyStyle($input, $output);

        $dataflowType = $this->registry->getDataflowType($fqcnOrAlias);
        if ($dataflowType instanceof LoggerAwareInterface && isset($this->logger)) {
            $dataflowType->setLogger($this->logger);
        }

        $result = $dataflowType->process($options);

        $io->writeln('Executed: '.$result->getName());
        $io->writeln('Start time: '.$result->getStartTime()->format('Y/m/d H:i:s'));
        $io->writeln('End time: '.$result->getEndTime()->format('Y/m/d H:i:s'));
        $io->writeln('Success: '.$result->getSuccessCount());

        if ($result->hasErrors()) {
            $io->error("Errors: {$result->getErrorCount()}\nExceptions traces are available in the logs.");

            return 1;
        }

        return 0;
    }
}
