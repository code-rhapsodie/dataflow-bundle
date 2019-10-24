<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Repository;

use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use CodeRhapsodie\DataflowBundle\Entity\Job;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

/**
 * Repository.
 *
 * @codeCoverageIgnore
 */
class JobRepository extends EntityRepository
{
    public function findOneshotDataflows(): iterable
    {
        return $this->findBy([
            'scheduledDataflow' => null,
            'status' => Job::STATUS_PENDING,
        ]);
    }

    public function findPendingForScheduledDataflow(ScheduledDataflow $scheduled): ?Job
    {
        return $this->findOneBy([
            'scheduledDataflow' => $scheduled->getId(),
            'status' => Job::STATUS_PENDING,
        ]);
    }

    public function findNextPendingDataflow(): ?Job
    {
        $criteria = (new Criteria())
            ->where(Criteria::expr()->lte('requestedDate', new \DateTime()))
            ->andWhere(Criteria::expr()->eq('status', Job::STATUS_PENDING))
            ->orderBy(['requestedDate' => Criteria::ASC])
            ->setMaxResults(1)
        ;

        return $this->matching($criteria)->first() ?: null;
    }

    public function findLastForDataflowId(int $dataflowId): ?Job
    {
        return $this->findOneBy(['scheduledDataflow' => $dataflowId], ['requestedDate' => 'desc']);
    }

    public function findLatests(): iterable
    {
        return $this->findBy([], ['requestedDate' => 'desc'], 20);
    }

    public function findForScheduled(int $id): iterable
    {
        return $this->findBy(['scheduledDataflow' => $id], ['requestedDate' => 'desc'], 20);
    }

    public function save(Job $job)
    {
        $this->_em->persist($job);
        $this->_em->flush();
    }
}
