<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Factory\ConnectionFactory;
use CodeRhapsodie\DataflowBundle\Gateway\JobGateway;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @codeCoverageIgnore
 */
#[AsCommand('code-rhapsodie:dataflow:job:show', 'Display job details for schedule or specific job', help: <<<'TXT'
The <info>%command.name%</info> display job details for schedule or specific job.
TXT)]
final readonly class JobShowCommand
{
    private const STATUS_MAPPING = [
        Job::STATUS_PENDING => 'Pending',
        Job::STATUS_RUNNING => 'Running',
        Job::STATUS_COMPLETED => 'Completed',
        Job::STATUS_QUEUED => 'Queued',
        Job::STATUS_CRASHED => 'Crashed',
    ];

    public function __construct(private JobGateway $jobGateway, private ConnectionFactory $connectionFactory)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option('Id of the job to get details')] ?int $jobId = null,
        #[Option('Id of schedule for last execution details')] ?int $scheduleId = null,
        #[Option('Display full details')] bool $details = false,
        #[Option('Define the DBAL connection to use')] ?string $connection = null,
    ): int {
        if ($connection !== null) {
            $this->connectionFactory->setConnectionName($connection);
        }

        if ($jobId && $scheduleId) {
            $io->error('You must use `job-id` OR `schedule-id` option, not the 2 in the same time.');

            return 1;
        }

        if ($scheduleId) {
            $job = $this->jobGateway->findLastForDataflowId($scheduleId);
        } elseif ($jobId) {
            $job = $this->jobGateway->find($jobId);
        } else {
            $io->error('You must pass `job-id` or `schedule-id` option.');

            return 2;
        }

        if ($job === null) {
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
            ['Errors', \count((array) $job->getExceptions())],
            ['Status', $this->translateStatus($job->getStatus())],
        ];
        if ($details) {
            $display[] = ['Type', $job->getDataflowType()];
            $display[] = ['Options', json_encode($job->getOptions(), \JSON_THROW_ON_ERROR)];
            $io->section('Summary');
        }

        $io->table(['Field', 'Value'], $display);
        if ($details) {
            $io->section('Exceptions');
            $exceptions = array_map(static fn (string $exception) => substr($exception, 0, 900).'…', $job->getExceptions());

            $io->write($exceptions);
        }

        return 0;
    }

    private function translateStatus(int $status): string
    {
        return self::STATUS_MAPPING[$status] ?? 'Unknown status';
    }
}
