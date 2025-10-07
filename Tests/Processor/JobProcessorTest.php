<?php

namespace CodeRhapsodie\DataflowBundle\Tests\Processor;

use CodeRhapsodie\DataflowBundle\DataflowType\DataflowTypeInterface;
use CodeRhapsodie\DataflowBundle\DataflowType\Result;
use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Event\Events;
use CodeRhapsodie\DataflowBundle\Event\ProcessingEvent;
use CodeRhapsodie\DataflowBundle\Gateway\JobGateway;
use CodeRhapsodie\DataflowBundle\Processor\JobProcessor;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JobProcessorTest extends TestCase
{
    private JobProcessor $processor;
    private JobRepository|MockObject $repository;
    private DataflowTypeRegistryInterface|MockObject $registry;
    private EventDispatcherInterface|MockObject $dispatcher;
    private JobGateway|MockObject $jobGateway;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(JobRepository::class);
        $this->registry = $this->createMock(DataflowTypeRegistryInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->jobGateway = $this->createMock(JobGateway::class);

        $this->processor = new JobProcessor($this->repository, $this->registry, $this->dispatcher, $this->jobGateway);
    }

    public function testProcess()
    {
        $now = new \DateTimeImmutable();
        $job = (new Job())
            ->setStatus(Job::STATUS_PENDING)
            ->setDataflowType($type = 'type')
            ->setOptions($options = ['option1' => 'value1'])
        ;

        $matcher = $this->exactly(2);
        $this->dispatcher
            ->expects($matcher)
            ->method('dispatch')
            ->with(
                $this->callback(fn($arg) => $arg instanceof ProcessingEvent && $arg->getJob() === $job),
                $this->callback(fn($arg) => match ($matcher->numberOfInvocations()) {
                    1 => $arg === Events::BEFORE_PROCESSING,
                    2 => $arg === Events::AFTER_PROCESSING,
                    default => false,
                })
            );

        $dataflowType = $this->createMock(DataflowTypeInterface::class);

        $this->registry
            ->expects($this->once())
            ->method('getDataflowType')
            ->with($type)
            ->willReturn($dataflowType)
        ;

        $bag = [new \Exception('message1')];

        $result = new Result('name', new \DateTimeImmutable(), $end = new \DateTimeImmutable(), $count = 10, $bag);

        $dataflowType
            ->expects($this->once())
            ->method('process')
            ->with($options)
            ->willReturn($result)
        ;

        $this->repository
            ->expects($this->exactly(2))
            ->method('save')
        ;

        $this->processor->process($job);

        $this->assertGreaterThanOrEqual($now, $job->getStartTime());
        $this->assertSame(Job::STATUS_COMPLETED, $job->getStatus());
        $this->assertSame($end, $job->getEndTime());
        $this->assertSame($count - count($bag), $job->getCount());
    }
}
