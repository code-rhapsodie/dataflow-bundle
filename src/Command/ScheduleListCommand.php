<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @codeCoverageIgnore
 */
#[AsCommand('code-rhapsodie:dataflow:schedule:list', 'List scheduled dataflows', help: <<<'TXT'
The <info>%command.name%</info> lists all scheduled dataflows.
TXT)]
final readonly class ScheduleListCommand
{
    public function __construct(private ScheduledDataflowRepository $scheduledDataflowRepository, private ConnectionFactory $connectionFactory)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option('Define the DBAL connection to use')] ?string $connection = null,
    ): int {
        if ($connection !== null) {
            $this->connectionFactory->setConnectionName($connection);
        }

        $display = [];
        $schedules = $this->scheduledDataflowRepository->listAllOrderedByLabel();
        foreach ($schedules as $schedule) {
            $display[] = [
                $schedule['id'],
                $schedule['label'],
                $schedule['enabled'] ? 'yes' : 'no',
                $schedule['startTime'] ? (new \DateTime($schedule['startTime']))->format('Y-m-d H:i:s') : '-',
                $schedule['next'] ? (new \DateTime($schedule['next']))->format('Y-m-d H:i:s') : '-',
            ];
        }

        $io->table(['id', 'label', 'enabled?', 'last execution', 'next execution'], $display);

        return 0;
    }
}
