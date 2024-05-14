<?php

namespace CodeRhapsodie\DataflowBundle\Tests\Runner;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\MessengerMode\JobMessage;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use CodeRhapsodie\DataflowBundle\Runner\MessengerDataflowRunner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerDataflowRunnerTest extends TestCase
{
    private MessengerDataflowRunner $runner;
    private JobRepository|MockObject $repository;
    private MessageBusInterface|MockObject $bus;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(JobRepository::class);
        $this->bus = $this->createMock(MessageBusInterface::class);

        $this->runner = new MessengerDataflowRunner($this->repository, $this->bus);
    }

    public function testRunPendingDataflows()
    {
        $job1 = (new Job())->setId($id1 = 10);
        $job2 = (new Job())->setId($id2 = 20);

        $this->repository
            ->expects($this->exactly(3))
            ->method('findNextPendingDataflow')
            ->willReturnOnConsecutiveCalls($job1, $job2, null)
        ;
        $matcher = $this->exactly(2);
        $this->repository
            ->expects($matcher)
            ->method('save')
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

        $matcher = $this->exactly(2);
        $this->bus
            ->expects($matcher)
            ->method('dispatch')
            ->with($this->callback(function ($arg) use ($matcher, $id1, $id2) {
                switch ($matcher->numberOfInvocations()) {
                    case 1:
                        return $arg instanceof JobMessage && $arg->getJobId() === $id1;
                    case 2:
                        return $arg instanceof JobMessage && $arg->getJobId() === $id2;
                    default:
                        return false;
                }
            }))
            ->willReturnOnConsecutiveCalls(
                new Envelope(new JobMessage($id1)),
                new Envelope(new JobMessage($id2))
            )
        ;

        $this->runner->runPendingDataflows();

        $this->assertSame(Job::STATUS_QUEUED, $job1->getStatus());
        $this->assertSame(Job::STATUS_QUEUED, $job2->getStatus());
    }
}
