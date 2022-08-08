<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Runner;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\MessengerMode\JobMessage;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerDataflowRunner implements PendingDataflowRunnerInterface
{
    public function __construct(private JobRepository $repository, private MessageBusInterface $bus)
    {
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
