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
    private PendingDataflowRunner $runner;
    private JobRepository|MockObject $repository;
    private JobProcessorInterface|MockObject $processor;

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

        $matcher = $this->exactly(2);
        $this->processor
            ->expects($matcher)
            ->method('process')
            ->with($this->callback(function ($arg) use ($matcher, $job1, $job2) {
                switch ($matcher->numberOfInvocations()) {
                    case 1:
                        return $arg === $job1;
                    case 2:
                        return $arg === $job2;
                    default:
                        return false;
                }
            }))
        ;

        $this->runner->runPendingDataflows();
    }
}
