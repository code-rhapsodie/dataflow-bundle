<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;

/**
 * @codeCoverageIgnore
 */
class ScheduleListCommand extends Command
{
    protected static $defaultName = 'code-rhapsodie:dataflow:schedule:list';

    /** @var ScheduledDataflowRepository */
    private $scheduledDataflowRepository;

    /** @var ConnectionFactory */
    private $connectionFactory;

    public function __construct(ScheduledDataflowRepository $scheduledDataflowRepository, ConnectionFactory $connectionFactory)
    {
        parent::__construct();

        $this->scheduledDataflowRepository = $scheduledDataflowRepository;
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('List scheduled dataflows')
            ->setHelp('The <info>%command.name%</info> lists all scheduled dataflows.')
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
