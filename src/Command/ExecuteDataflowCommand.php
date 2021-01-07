<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
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
class ExecuteDataflowCommand extends Command
{
    protected static $defaultName = 'code-rhapsodie:dataflow:execute';

    /** @var DataflowTypeRegistryInterface */
    private $registry;

    /** @var ConnectionFactory */
    private $connectionFactory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(DataflowTypeRegistryInterface $registry, ConnectionFactory $connectionFactory, LoggerInterface $logger)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->connectionFactory = $connectionFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Runs one dataflow type with provided options')
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getOption('connection')) {
            $this->connectionFactory->setConnectionName($input->getOption('connection'));
        }
        $fqcnOrAlias = $input->getArgument('fqcn');
        $options = json_decode($input->getArgument('options'), true);
        $io = new SymfonyStyle($input, $output);

        $dataflowType = $this->registry->getDataflowType($fqcnOrAlias);
        if ($dataflowType instanceof LoggerAwareInterface) {
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
