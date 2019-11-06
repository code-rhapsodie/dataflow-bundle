<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @codeCoverageIgnore
 */
class ScheduleListCommand extends Command
{
    protected static $defaultName = 'code-rhapsodie:dataflow:schedule:list';

    /** @var ScheduledDataflowRepository */
    private $scheduledDataflowRepository;

    public function __construct(ScheduledDataflowRepository $scheduledDataflowRepository)
    {
        parent::__construct();

        $this->scheduledDataflowRepository = $scheduledDataflowRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('List scheduled dataflows')
            ->setHelp('The <info>%command.name%</info> lists all scheduled dataflows.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
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
