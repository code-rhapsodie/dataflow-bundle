<?php

namespace CodeRhapsodie\DataflowBundle\Tests\Runner;

use CodeRhapsodie\DataflowBundle\DataflowType\DataflowTypeInterface;
use CodeRhapsodie\DataflowBundle\DataflowType\Result;
use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Event\Events;
use CodeRhapsodie\DataflowBundle\Event\ProcessingEvent;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use CodeRhapsodie\DataflowBundle\Runner\PendingDataflowRunner;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PendingDataflowRunnerTest extends TestCase
{
    /** @var PendingDataflowRunner */
    private $runner;

    /** @var EntityManagerInterface|MockObject */
    private $em;

    /** @var JobRepository|MockObject */
    private $repository;

    /** @var DataflowTypeRegistryInterface|MockObject */
    private $registry;

    /** @var EventDispatcherInterface|MockObject */
    private $dispatcher;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(JobRepository::class);
        $this->registry = $this->createMock(DataflowTypeRegistryInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->runner = new PendingDataflowRunner($this->em, $this->repository, $this->registry, $this->dispatcher);
    }

    public function testRunPendingDataflows()
    {
        $now = new \DateTime();
        $job1 = (new Job())
            ->setStatus(Job::STATUS_PENDING)
            ->setDataflowType($type1 = 'type1')
            ->setOptions($options1 = ['option1' => 'value1'])
        ;
        $job2 = (new Job())
            ->setStatus(Job::STATUS_PENDING)
            ->setDataflowType($type2 = 'type2')
            ->setOptions($options2 = ['option2' => 'value2'])
        ;

        $this->repository
            ->expects($this->exactly(3))
            ->method('findNextPendingDataflow')
            ->willReturnOnConsecutiveCalls($job1, $job2, null)
        ;

        $this->dispatcher
            ->expects($this->exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                [
                    Events::BEFORE_PROCESSING,
                    $this->callback(function (ProcessingEvent $event) use ($job1) {
                        return $event->getJob() === $job1;
                    })
                ],
                [
                    Events::AFTER_PROCESSING,
                    $this->callback(function (ProcessingEvent $event) use ($job1) {
                        return $event->getJob() === $job1;
                    })
                ],
                [
                    Events::BEFORE_PROCESSING,
                    $this->callback(function (ProcessingEvent $event) use ($job2) {
                        return $event->getJob() === $job2;
                    })
                ],
                [
                    Events::AFTER_PROCESSING,
                    $this->callback(function (ProcessingEvent $event) use ($job2) {
                        return $event->getJob() === $job2;
                    })
                ]
            )
        ;

        $dataflowType1 = $this->createMock(DataflowTypeInterface::class);
        $dataflowType2 = $this->createMock(DataflowTypeInterface::class);

        $this->registry
            ->expects($this->exactly(2))
            ->method('getDataflowType')
            ->withConsecutive([$type1], [$type2])
            ->willReturnOnConsecutiveCalls($dataflowType1, $dataflowType2)
        ;

        $bag1 = [new \Exception('message1')];
        $bag2 = [new \Exception('message2')];

        $result1 = new Result('name', new \DateTime(), $end1 = new \DateTime(), $count1 = 10, $bag1);
        $result2 = new Result('name', new \DateTime(), $end2 = new \DateTime(), $count2 = 20, $bag2);

        $dataflowType1
            ->expects($this->once())
            ->method('process')
            ->with($options1)
            ->willReturn($result1)
        ;
        $dataflowType2
            ->expects($this->once())
            ->method('process')
            ->with($options2)
            ->willReturn($result2)
        ;

        $this->em
            ->expects($this->exactly(4))
            ->method('flush')
        ;

        $this->runner->runPendingDataflows();

        $this->assertGreaterThanOrEqual($now, $job1->getStartTime());
        $this->assertSame(Job::STATUS_COMPLETED, $job1->getStatus());
        $this->assertSame($end1, $job1->getEndTime());
        $this->assertSame($count1 - count($bag1), $job1->getCount());

        $this->assertGreaterThanOrEqual($now, $job2->getStartTime());
        $this->assertSame(Job::STATUS_COMPLETED, $job2->getStatus());
        $this->assertSame($end2, $job2->getEndTime());
        $this->assertSame($count2 - count($bag2), $job2->getCount());
    }
}
