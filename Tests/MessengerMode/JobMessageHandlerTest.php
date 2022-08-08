<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Tests\MessengerMode;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\MessengerMode\JobMessage;
use CodeRhapsodie\DataflowBundle\MessengerMode\JobMessageHandler;
use CodeRhapsodie\DataflowBundle\Processor\JobProcessorInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobMessageHandlerTest extends TestCase
{
    private \CodeRhapsodie\DataflowBundle\Repository\JobRepository|\PHPUnit\Framework\MockObject\MockObject $repository;

    private \CodeRhapsodie\DataflowBundle\Processor\JobProcessorInterface|\PHPUnit\Framework\MockObject\MockObject $processor;

    private \CodeRhapsodie\DataflowBundle\MessengerMode\JobMessageHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(JobRepository::class);
        $this->processor = $this->createMock(JobProcessorInterface::class);

        $this->handler = new JobMessageHandler($this->repository, $this->processor);
    }

    public function testGetHandledMessages()
    {
        $this->assertSame([JobMessage::class], JobMessageHandler::getHandledMessages());
    }

    public function testInvoke()
    {
        $message = new JobMessage($id = 32);

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($job = new Job())
        ;

        $this->processor
            ->expects($this->once())
            ->method('process')
            ->with($job)
        ;

        ($this->handler)($message);
    }
}
