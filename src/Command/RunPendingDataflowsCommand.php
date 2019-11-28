<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Manager\ScheduledDataflowManagerInterface;
use CodeRhapsodie\DataflowBundle\Runner\PendingDataflowRunnerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs dataflows according to user-defined schedule.
 *
 * @codeCoverageIgnore
 */
class RunPendingDataflowsCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'code-rhapsodie:dataflow:run-pending';

    /** @var ScheduledDataflowManagerInterface */
    private $manager;

    /** @var PendingDataflowRunnerInterface */
    private $runner;

    /** @var ConnectionFactory */
    private $connectionFactory;

    public function __construct(ScheduledDataflowManagerInterface $manager, PendingDataflowRunnerInterface $runner, ConnectionFactory $connectionFactory)
    {
        parent::__construct();

        $this->manager = $manager;
        $this->runner = $runner;
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Runs dataflows based on the scheduled defined in the UI.')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command runs dataflows according to the schedule defined in the UI by the user.
EOF
            )
            ->addOption('connection', null, InputOption::VALUE_REQUIRED, 'Define the DBAL connection to use');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        if (null !== $input->getOption('connection')) {
            $this->connectionFactory->setConnectionName($input->getOption('connection'));
        }

        $this->manager->createJobsFromScheduledDataflows();
        $this->runner->runPendingDataflows();

        $this->release();

        return 0;
    }
}
