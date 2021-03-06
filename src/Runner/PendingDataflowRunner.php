<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Runner;

use CodeRhapsodie\DataflowBundle\Processor\JobProcessorInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;

class PendingDataflowRunner implements PendingDataflowRunnerInterface
{
    /** @var JobRepository */
    private $repository;

    /** @var JobProcessorInterface */
    private $processor;

    public function __construct(JobRepository $repository, JobProcessorInterface $processor)
    {
        $this->repository = $repository;
        $this->processor = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function runPendingDataflows(): void
    {
        while (null !== ($job = $this->repository->findNextPendingDataflow())) {
            $this->processor->process($job);
        }
    }
}
