<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Manager;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Doctrine\DBAL\Connection;

/**
 * Handles scheduled dataflows execution dates based on their frequency.
 */
class ScheduledDataflowManager implements ScheduledDataflowManagerInterface
{
    public function __construct(private Connection $connection, private ScheduledDataflowRepository $scheduledDataflowRepository, private JobRepository $jobRepository)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createJobsFromScheduledDataflows(): void
    {
        $this->connection->beginTransaction();
        try {
            foreach ($this->scheduledDataflowRepository->findReadyToRun() as $scheduled) {
                if (null !== $this->jobRepository->findPendingForScheduledDataflow($scheduled)) {
                    continue;
                }

                $this->createPendingForScheduled($scheduled);
                $this->updateScheduledDataflowNext($scheduled);
            }
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
        $this->connection->commit();
    }

    private function updateScheduledDataflowNext(ScheduledDataflow $scheduled): void
    {
        $interval = \DateInterval::createFromDateString($scheduled->getFrequency());
        $next = clone $scheduled->getNext();
        $now = new \DateTime();

        while ($next < $now) {
            $next->add($interval);
        }

        $scheduled->setNext($next);
        $this->scheduledDataflowRepository->save($scheduled);
    }

    private function createPendingForScheduled(ScheduledDataflow $scheduled): void
    {
        $this->jobRepository->save(Job::createFromScheduledDataflow($scheduled));
    }
}
