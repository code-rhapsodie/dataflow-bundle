<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Gateway;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\ExceptionsHandler\ExceptionHandlerInterface;
use CodeRhapsodie\DataflowBundle\ExceptionsHandler\NullExceptionHandler;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;

class JobGateway
{
    public function __construct(private readonly JobRepository $repository, private readonly ExceptionHandlerInterface $exceptionHandler)
    {
    }

    public function find(int $jobId): ?Job
    {
        $job = $this->repository->find($jobId);

        return $this->loadExceptions($job);
    }

    public function save(Job $job): void
    {
        if (!$this->exceptionHandler instanceof NullExceptionHandler) {
            $this->exceptionHandler->save($job->getId(), $job->getExceptions());
            $job->setExceptions([]);
        }

        $this->repository->save($job);
    }

    public function findLastForDataflowId(int $scheduleId): ?Job
    {
        $job = $this->repository->findLastForDataflowId($scheduleId);

        return $this->loadExceptions($job);
    }

    private function loadExceptions(?Job $job): ?Job
    {
        if ($job === null || $this->exceptionHandler instanceof NullExceptionHandler) {
            return $job;
        }

        $this->exceptionHandler->save($job->getId(), $job->getExceptions());

        return $job->setExceptions($this->exceptionHandler->find($job->getId()));
    }
}
