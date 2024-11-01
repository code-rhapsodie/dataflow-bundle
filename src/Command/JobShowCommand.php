<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @codeCoverageIgnore
 */
#[AsCommand('code-rhapsodie:dataflow:job:show', 'Display job details for schedule or specific job')]
class JobShowCommand extends Command
{
    private const STATUS_MAPPING = [
        Job::STATUS_PENDING => 'Pending',
        Job::STATUS_RUNNING => 'Running',
        Job::STATUS_COMPLETED => 'Completed',
    ];

    public function __construct(private JobRepository $jobRepository, private ConnectionFactory $connectionFactory)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setHelp('The <info>%command.name%</info> display job details for schedule or specific job.')
            ->addOption('job-id', null, InputOption::VALUE_REQUIRED, 'Id of the job to get details')
            ->addOption('schedule-id', null, InputOption::VALUE_REQUIRED, 'Id of schedule for last execution details')
            ->addOption('details', null, InputOption::VALUE_NONE, 'Display full details')
            ->addOption('connection', null, InputOption::VALUE_REQUIRED, 'Define the DBAL connection to use');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null !== $input->getOption('connection')) {
            $this->connectionFactory->setConnectionName($input->getOption('connection'));
        }

        $io = new SymfonyStyle($input, $output);

        $jobId = (int) $input->getOption('job-id');
        $scheduleId = (int) $input->getOption('schedule-id');
        if ($jobId && $scheduleId) {
            $io->error('You must use `job-id` OR `schedule-id` option, not the 2 in the same time.');

            return 1;
        }

        if ($scheduleId) {
            $job = $this->jobRepository->findLastForDataflowId($scheduleId);
        } elseif ($jobId) {
            $job = $this->jobRepository->find($jobId);
        } else {
            $io->error('You must pass `job-id` or `schedule-id` option.');

            return 2;
        }

        if (null === $job) {
            $io->error('Cannot find job :/');

            return 3;
        }

        /** @var Job $job */
        $display = [
            ['Job id', $job->getId()],
            ['Label', $job->getLabel()],
            ['Requested at', $job->getRequestedDate()->format('Y-m-d H:i:s')],
            ['Started at', $job->getStartTime() ? $job->getStartTime()->format('Y-m-d H:i:s') : '-'],
            ['Ended at', $job->getEndTime() ? $job->getEndTime()->format('Y-m-d H:i:s') : '-'],
            ['Object number', $job->getCount()],
            ['Errors', count((array) $job->getExceptions())],
            ['Status', $this->translateStatus($job->getStatus())],
        ];
        if ($input->getOption('details')) {
            $display[] = ['Type', $job->getDataflowType()];
            $display[] = ['Options', json_encode($job->getOptions(), JSON_THROW_ON_ERROR)];
            $io->section('Summary');
        }

        $io->table(['Field', 'Value'], $display);
        if ($input->getOption('details')) {
            $io->section('Exceptions');
            $exceptions = array_map(fn(string $exception) => substr($exception, 0, 900).'…', $job->getExceptions());

            $io->write($exceptions);
        }

        return 0;
    }

    private function translateStatus(int $status): string
    {
        return self::STATUS_MAPPING[$status] ?? 'Unknown status';
    }
}
