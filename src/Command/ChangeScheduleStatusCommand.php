<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @codeCoverageIgnore
 */
#[AsCommand('code-rhapsodie:dataflow:schedule:change-status', 'Change schedule status', help: <<<'TXT'
The <info>%command.name%</info> command able you to change schedule status.
TXT)]
final readonly class ChangeScheduleStatusCommand
{
    public function __construct(private ScheduledDataflowRepository $scheduledDataflowRepository, private ConnectionFactory $connectionFactory)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Id of the schedule')] int $scheduleId,
        #[Option('Enable the schedule')] ?bool $enable = null,
        #[Option('Disable the schedule')] ?bool $disable = null,
        #[Option('Define the DBAL connection to use')] ?string $connection = null,
    ): int {
        if ($connection !== null) {
            $this->connectionFactory->setConnectionName($connection);
        }

        /** @var ScheduledDataflow|null $schedule */
        $schedule = $this->scheduledDataflowRepository->find($scheduleId);

        if (!$schedule) {
            $io->error(\sprintf('Cannot find scheduled dataflow with id "%d".', $scheduleId));

            return 1;
        }

        if ($enable !== null && $disable !== null) {
            $io->error('You cannot pass enable and disable options in the same time.');

            return 2;
        }
        if ($enable === null && $disable === null) {
            $io->error('You must pass enable or disable option.');

            return 3;
        }

        $enable = $enable ?? !$disable;

        try {
            $schedule->setEnabled($enable);
            $this->scheduledDataflowRepository->save($schedule);
            $io->success(\sprintf('Schedule with id "%s" has been successfully updated.', $schedule->getId()));
        } catch (\Exception $e) {
            $io->error(\sprintf('An error occured when changing schedule status : "%s".', $e->getMessage()));

            return 4;
        }

        return 0;
    }
}
