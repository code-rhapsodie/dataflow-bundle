<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Manager;

use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Handles scheduled dataflows execution dates based on their frequency.
 */
class ScheduledDataflowManager implements ScheduledDataflowManagerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var ScheduledDataflowRepository */
    private $scheduledDataflowRepository;

    /** @var JobRepository */
    private $jobRepository;

    public function __construct(EntityManagerInterface $em, ScheduledDataflowRepository $scheduledDataflowRepository, JobRepository $jobRepository)
    {
        $this->em = $em;
        $this->scheduledDataflowRepository = $scheduledDataflowRepository;
        $this->jobRepository = $jobRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createJobsFromScheduledDataflows(): void
    {
        foreach ($this->scheduledDataflowRepository->findReadyToRun() as $scheduled) {
            if (null !== $this->jobRepository->findPendingForScheduledDataflow($scheduled)) {
                continue;
            }

            $this->createPendingForScheduled($scheduled);
            $this->updateScheduledDataflowNext($scheduled);
        }

        $this->em->flush();
    }

    /**
     * @param ScheduledDataflow $scheduled
     */
    private function updateScheduledDataflowNext(ScheduledDataflow $scheduled): void
    {
        $interval = \DateInterval::createFromDateString($scheduled->getFrequency());
        $next = clone $scheduled->getNext();
        $now = new \DateTime();

        while ($next < $now) {
            $next->add($interval);
        }

        $scheduled->setNext($next);
    }

    /**
     * @param ScheduledDataflow $scheduled
     */
    private function createPendingForScheduled(ScheduledDataflow $scheduled): void
    {
        $this->em->persist(Job::createFromScheduledDataflow($scheduled));
    }
}
