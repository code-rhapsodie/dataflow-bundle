<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Manager\ScheduledDataflowManagerInterface;
use CodeRhapsodie\DataflowBundle\Runner\PendingDataflowRunnerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
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
#[AsCommand('code-rhapsodie:dataflow:run-pending', 'Runs dataflows based on the scheduled defined in the UI.')]
class RunPendingDataflowsCommand extends Command
{
    use LockableTrait;

    public function __construct(private ScheduledDataflowManagerInterface $manager, private PendingDataflowRunnerInterface $runner, private ConnectionFactory $connectionFactory)
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
The <info>%command.name%</info> command runs dataflows according to the schedule defined in the UI by the user.
EOF
            )
            ->addOption('connection', null, InputOption::VALUE_REQUIRED, 'Define the DBAL connection to use');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
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
