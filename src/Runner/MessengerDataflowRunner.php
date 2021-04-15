<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Runner;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\MessengerMode\JobMessage;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerDataflowRunner implements PendingDataflowRunnerInterface
{
    /** @var JobRepository */
    private $repository;

    /** @var MessageBusInterface */
    private $bus;

    public function __construct(JobRepository $repository, MessageBusInterface $bus)
    {
        $this->repository = $repository;
        $this->bus = $bus;
    }

    public function runPendingDataflows(): void
    {
        while (null !== ($job = $this->repository->findNextPendingDataflow())) {
            $this->bus->dispatch(new JobMessage($job->getId()));
            $job->setStatus(Job::STATUS_QUEUED);
            $this->repository->save($job);
        }
    }
}
