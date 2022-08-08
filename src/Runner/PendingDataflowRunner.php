<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Runner;

use CodeRhapsodie\DataflowBundle\Processor\JobProcessorInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;

class PendingDataflowRunner implements PendingDataflowRunnerInterface
{
    public function __construct(private JobRepository $repository, private JobProcessorInterface $processor)
    {
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
