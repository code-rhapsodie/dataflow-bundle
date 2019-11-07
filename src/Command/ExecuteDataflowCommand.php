<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;

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

    public function __construct(DataflowTypeRegistryInterface $registry, ConnectionFactory $connectionFactory)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->connectionFactory = $connectionFactory;
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

        $dataflowType = $this->registry->getDataflowType($fqcnOrAlias);
        $result = $dataflowType->process($options);

        $output->writeln('Executed: '.$result->getName());
        $output->writeln('Start time: '.$result->getStartTime()->format('Y/m/d H:i:s'));
        $output->writeln('End time: '.$result->getEndTime()->format('Y/m/d H:i:s'));
        $output->writeln('Success: '.$result->getSuccessCount());

        return 0;
    }
}
