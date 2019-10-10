<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Repository;

use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

/**
 * Repository for the ScheduledDataflow entity.
 */
class ScheduledDataflowRepository extends EntityRepository
{
    /**
     * Finds all enabled scheduled dataflows with a passed next run date.
     *
     * @return ScheduledDataflow[]
     */
    public function findReadyToRun(): iterable
    {
        $criteria = (new Criteria())
            ->where(Criteria::expr()->lte('next', new \DateTime()))
            ->andWhere(Criteria::expr()->eq('enabled', 1))
            ->orderBy(['next' => Criteria::ASC])
        ;

        return $this->matching($criteria);
    }

    public function findAllOrderedByLabel(): iterable
    {
        return $this->findBy([], ['label' => 'asc']);
    }

    public function listAllOrderedByLabel(): array
    {
        $query = $this->createQueryBuilder('w')
            ->select('w.id', 'w.label', 'w.enabled', 'w.next', 'max(j.startTime) as startTime')
            ->leftJoin('w.jobs', 'j')
            ->orderBy('w.label', 'ASC')
            ->groupBy('w.id');

        return $query->getQuery()->execute();
    }

    public function save(ScheduledDataflow $scheduledDataflow)
    {
        $this->_em->persist($scheduledDataflow);
        $this->_em->flush();
    }

    public function delete(int $id): void
    {
        $dataflow = $this->find($id);

        $this->_em->remove($dataflow);
        $this->_em->flush();
    }
}
