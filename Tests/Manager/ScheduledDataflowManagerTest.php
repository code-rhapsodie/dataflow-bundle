<?php

namespace CodeRhapsodie\DataflowBundle\Tests\Manager;

use CodeRhapsodie\DataflowBundle\DataflowType\DataflowTypeInterface;
use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use CodeRhapsodie\DataflowBundle\Exceptions\UnknownDataflowTypeException;
use CodeRhapsodie\DataflowBundle\Manager\ScheduledDataflowManager;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScheduledDataflowManagerTest extends TestCase
{
    /** @var ScheduledDataflowManager */
    private $manager;

    /** @var EntityManagerInterface|MockObject */
    private $em;

    /** @var ScheduledDataflowRepository|MockObject */
    private $scheduledDataflowRepository;

    /** @var JobRepository|MockObject */
    private $jobRepository;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->scheduledDataflowRepository = $this->createMock(ScheduledDataflowRepository::class);
        $this->jobRepository = $this->createMock(JobRepository::class);

        $this->manager = new ScheduledDataflowManager($this->em, $this->scheduledDataflowRepository, $this->jobRepository);
    }

    public function testCreateJobsFromScheduledDataflows()
    {
        $scheduled1 = new ScheduledDataflow();
        $scheduled2 = (new ScheduledDataflow())
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

        $this->jobRepository
            ->expects($this->exactly(2))
            ->method('findPendingForScheduledDataflow')
            ->withConsecutive([$scheduled1], [$scheduled2])
            ->willReturnOnConsecutiveCalls(new Job(), null)
        ;

        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with(
                $this->callback(function (Job $job) use ($type, $options, $next, $label, $scheduled2) {
                    return (
                        $job->getStatus() === Job::STATUS_PENDING
                        && $job->getDataflowType() === $type
                        && $job->getOptions() === $options
                        && $job->getRequestedDate() == $next
                        && $job->getLabel() === $label
                        && $job->getScheduledDataflow() === $scheduled2
                    );
                })
            )
        ;

        $this->em
            ->expects($this->once())
            ->method('flush')
        ;

        $this->manager->createJobsFromScheduledDataflows();

        $this->assertEquals($next->add(\DateInterval::createFromDateString($frequency)), $scheduled2->getNext());
    }
}
