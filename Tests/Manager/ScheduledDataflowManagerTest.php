<?php

namespace CodeRhapsodie\DataflowBundle\Tests\Manager;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use CodeRhapsodie\DataflowBundle\Manager\ScheduledDataflowManager;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScheduledDataflowManagerTest extends TestCase
{
    private ScheduledDataflowManager $manager;
    private Connection|MockObject $connection;
    private ScheduledDataflowRepository|MockObject $scheduledDataflowRepository;
    private JobRepository|MockObject $jobRepository;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->scheduledDataflowRepository = $this->createMock(ScheduledDataflowRepository::class);
        $this->jobRepository = $this->createMock(JobRepository::class);

        $this->manager = new ScheduledDataflowManager($this->connection, $this->scheduledDataflowRepository, $this->jobRepository);
    }

    public function testCreateJobsFromScheduledDataflows()
    {
        $scheduled1 = new ScheduledDataflow();
        $scheduled2 = (new ScheduledDataflow())
            ->setId(-1)
            ->setDataflowType($type = 'testType')
            ->setOptions($options = ['opt' => 'val'])
            ->setNext($next = new \DateTime())
            ->setLabel($label = 'testLabel')
            ->setFrequency($frequency = '1 year')
        ;

        $this->scheduledDataflowRepository
            ->expects($this->once())
            ->method('findReadyToRun')
            ->willReturn([$scheduled1, $scheduled2])
        ;

        $matcher = $this->exactly(2);
        $this->jobRepository
            ->expects($matcher)
            ->method('findPendingForScheduledDataflow')
            ->with($this->callback(function ($arg) use ($matcher, $scheduled1, $scheduled2) {
                switch ($matcher->numberOfInvocations()) {
                    case 1:
                        return $arg === $scheduled1;
                    case 2:
                        return $arg === $scheduled2;
                    default:
                        return false;
                }
            }))
            ->willReturnOnConsecutiveCalls(new Job(), null)
        ;

        $this->connection
            ->expects($this->once())
            ->method('beginTransaction')
        ;
        $this->jobRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(fn(Job $job) => $job->getStatus() === Job::STATUS_PENDING
                && $job->getDataflowType() === $type
                && $job->getOptions() === $options
                && $job->getRequestedDate() == $next
                && $job->getLabel() === $label
                && $job->getScheduledDataflowId() === $scheduled2->getId())
            )
        ;

        $this->scheduledDataflowRepository
            ->expects($this->once())
            ->method('save')
            ->with($scheduled2)
        ;

        $this->connection
            ->expects($this->once())
            ->method('commit')
        ;

        $this->manager->createJobsFromScheduledDataflows();

        $this->assertEquals($next->add(\DateInterval::createFromDateString($frequency)), $scheduled2->getNext());
    }

    public function testCreateJobsFromScheduledDataflowsWithError()
    {
        $scheduled1 = new ScheduledDataflow();

        $this->scheduledDataflowRepository
            ->expects($this->once())
            ->method('findReadyToRun')
            ->willReturn([$scheduled1])
        ;

        $this->jobRepository
            ->expects($this->exactly(1))
            ->method('findPendingForScheduledDataflow')
            ->with($scheduled1)
            ->willThrowException(new \Exception())
        ;

        $this->connection
            ->expects($this->once())
            ->method('beginTransaction')
        ;
        $this->jobRepository
            ->expects($this->never())
            ->method('save')
        ;

        $this->connection
            ->expects($this->never())
            ->method('commit')
        ;
        $this->connection
            ->expects($this->once())
            ->method('rollBack')
        ;

        $this->expectException(\Exception::class);

        $this->manager->createJobsFromScheduledDataflows();
    }
}
