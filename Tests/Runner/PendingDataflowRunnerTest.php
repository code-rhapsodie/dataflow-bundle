<?php

namespace CodeRhapsodie\DataflowBundle\Tests\Runner;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Processor\JobProcessorInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use CodeRhapsodie\DataflowBundle\Runner\PendingDataflowRunner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PendingDataflowRunnerTest extends TestCase
{
    private \CodeRhapsodie\DataflowBundle\Runner\PendingDataflowRunner $runner;

    private \CodeRhapsodie\DataflowBundle\Repository\JobRepository|\PHPUnit\Framework\MockObject\MockObject $repository;

    private \CodeRhapsodie\DataflowBundle\Processor\JobProcessorInterface|\PHPUnit\Framework\MockObject\MockObject $processor;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(JobRepository::class);
        $this->processor = $this->createMock(JobProcessorInterface::class);

        $this->runner = new PendingDataflowRunner($this->repository, $this->processor);
    }

    public function testRunPendingDataflows()
    {
        $job1 = new Job();
        $job2 = new Job();

        $this->repository
            ->expects($this->exactly(3))
            ->method('findNextPendingDataflow')
            ->willReturnOnConsecutiveCalls($job1, $job2, null)
        ;

        $this->processor
            ->expects($this->exactly(2))
            ->method('process')
            ->withConsecutive([$job1], [$job2])
        ;

        $this->runner->runPendingDataflows();
    }
}
