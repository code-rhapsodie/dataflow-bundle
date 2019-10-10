<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ChangeScheduleStatusCommand extends Command
{
    protected static $defaultName = 'code-rhapsodie:dataflow:schedule:change-status';

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
            ->setDescription('Change schedule status')
            ->setHelp('The <info>%command.name%</info> command able you to change schedule status.')
            ->addArgument('schedule-id', InputArgument::REQUIRED, 'Id of the schedule')
            ->addOption('enable', null, InputOption::VALUE_NONE, 'Enable the schedule')
            ->addOption('disable', null, InputOption::VALUE_NONE, 'Disable the schedule');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        /** @var ScheduledDataflow|null $schedule */
        $schedule = $this->scheduledDataflowRepository->find($input->getArgument('schedule-id'));

        if (!$schedule) {
            $io->error(sprintf('Cannot find scheduled dataflow with id "%d".', $input->getArgument('schedule-id')));

            return 1;
        }

        if ($input->getOption('enable') && $input->getOption('disable')) {
            $io->error('You cannot pass enable and disable options in the same time.');

            return 2;
        }
        if (!$input->getOption('enable') && !$input->getOption('disable')) {
            $io->error('You must pass enable or disable option.');

            return 3;
        }

        try {
            $schedule->setEnabled($input->getOption('enable'));
            $this->scheduledDataflowRepository->save($schedule);
            $io->success(sprintf('Schedule with id "%s" has been successfully updated.', $schedule->getId()));
        } catch (\Exception $e) {
            $io->error(sprintf('An error occured when changing schedule status : "%s".', $e->getMessage()));

            return 4;
        }

        return 0;
    }
}
