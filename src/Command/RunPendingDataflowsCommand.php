<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Manager\ScheduledDataflowManagerInterface;
use CodeRhapsodie\DataflowBundle\Runner\PendingDataflowRunnerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Runs dataflows according to user-defined schedule.
 *
 * @codeCoverageIgnore
 */
#[AsCommand('code-rhapsodie:dataflow:run-pending', 'Runs dataflows based on the scheduled defined in the UI.', help: <<<'TXT'
The <info>%command.name%</info> command runs dataflows according to the schedule defined in the UI by the user.
TXT)]
final class RunPendingDataflowsCommand
{
    use LockableTrait;

    public function __construct(private readonly ScheduledDataflowManagerInterface $manager, private readonly PendingDataflowRunnerInterface $runner, private readonly ConnectionFactory $connectionFactory)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option('Define the DBAL connection to use')] ?string $connection = null,
    ): int {
        if (!$this->lock()) {
            $io->writeln('The command is already running in another process.');

            return 0;
        }

        if ($connection !== null) {
            $this->connectionFactory->setConnectionName($connection);
        }

        $this->manager->createJobsFromScheduledDataflows();
        $this->runner->runPendingDataflows();

        $this->release();

        return 0;
    }
}
