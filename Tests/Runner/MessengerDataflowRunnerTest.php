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
    /** @var MessengerDataflowRunner */
    private $runner;

    /** @var JobRepository|MockObject */
    private $repository;

    /** @var MessageBusInterface|MockObject */
    private $bus;

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
        $this->repository
            ->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive([$job1], [$job2])
        ;

        $this->bus
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive([
                $this->callback(function ($message) use ($id1) {
                    return $message instanceof JobMessage && $message->getJobId() === $id1;
                })
            ], [
                $this->callback(function ($message) use ($id2) {
                    return $message instanceof JobMessage && $message->getJobId() === $id2;
                })
            ])
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
