<?php

namespace CodeRhapsodie\DataflowBundle\Tests\Processor;

use CodeRhapsodie\DataflowBundle\DataflowType\DataflowTypeInterface;
use CodeRhapsodie\DataflowBundle\DataflowType\Result;
use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Event\Events;
use CodeRhapsodie\DataflowBundle\Event\ProcessingEvent;
use CodeRhapsodie\DataflowBundle\Processor\JobProcessor;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JobProcessorTest extends TestCase
{
    private \CodeRhapsodie\DataflowBundle\Processor\JobProcessor $processor;

    private \CodeRhapsodie\DataflowBundle\Repository\JobRepository|\PHPUnit\Framework\MockObject\MockObject $repository;

    private \CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface|\PHPUnit\Framework\MockObject\MockObject $registry;

    private \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(JobRepository::class);
        $this->registry = $this->createMock(DataflowTypeRegistryInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->processor = new JobProcessor($this->repository, $this->registry, $this->dispatcher);
    }

    public function testProcess()
    {
        $now = new \DateTimeImmutable();
        $job = (new Job())
            ->setStatus(Job::STATUS_PENDING)
            ->setDataflowType($type = 'type')
            ->setOptions($options = ['option1' => 'value1'])
        ;

        // Symfony 3.4 to 4.4 call
        if (!class_exists(\Symfony\Contracts\EventDispatcher\Event::class)) {
            $this->dispatcher
                ->expects($this->exactly(2))
                ->method('dispatch')
                ->withConsecutive(
                    [
                        Events::BEFORE_PROCESSING,
                        $this->callback(fn(ProcessingEvent $event) => $event->getJob() === $job)
                    ],
                    [
                        Events::AFTER_PROCESSING,
                        $this->callback(fn(ProcessingEvent $event) => $event->getJob() === $job)
                    ],
                );
        } else { // Symfony 5.0+
            $this->dispatcher
                ->expects($this->exactly(2))
                ->method('dispatch')
                ->withConsecutive(
                    [
                        $this->callback(fn(ProcessingEvent $event) => $event->getJob() === $job),
                        Events::BEFORE_PROCESSING,
                    ],
                    [
                        $this->callback(fn(ProcessingEvent $event) => $event->getJob() === $job),
                        Events::AFTER_PROCESSING,
                    ],
                );
        }

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
