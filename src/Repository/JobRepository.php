<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Repository;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Repository.
 *
 * @codeCoverageIgnore
 */
class JobRepository
{
    use InitFromDbTrait;

    public const TABLE_NAME = 'cr_dataflow_job';

    private const FIELDS_TYPE = [
        'id' => ParameterType::INTEGER,
        'status' => ParameterType::INTEGER,
        'label' => ParameterType::STRING,
        'dataflow_type' => ParameterType::STRING,
        'options' => ParameterType::STRING,
        'requested_date' => 'datetime',
        'scheduled_dataflow_id' => ParameterType::INTEGER,
        'count' => ParameterType::INTEGER,
        'exceptions' => ParameterType::STRING,
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * JobRepository constructor.
     */
    public function __construct(private Connection $connection)
    {
    }

    public function find(int $jobId)
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->andWhere($qb->expr()->eq('id', $qb->createNamedParameter($jobId, ParameterType::INTEGER)))
        ;

        return $this->returnFirstOrNull($qb);
    }

    public function findOneshotDataflows(): iterable
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->andWhere($qb->expr()->isNull('scheduled_dataflow_id'))
            ->andWhere($qb->expr()->eq('status', $qb->createNamedParameter(Job::STATUS_PENDING, ParameterType::INTEGER)));
        $stmt = $qb->executeQuery();
        if (0 === $stmt->rowCount()) {
            return [];
        }
        while (false !== ($row = $stmt->fetchAssociative())) {
            yield Job::createFromArray($this->initDateTime($this->initArray($row)));
        }
    }

    public function findPendingForScheduledDataflow(ScheduledDataflow $scheduled): ?Job
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->andWhere($qb->expr()->eq('scheduled_dataflow_id', $qb->createNamedParameter($scheduled->getId(), ParameterType::INTEGER)))
            ->andWhere($qb->expr()->eq('status', $qb->createNamedParameter(Job::STATUS_PENDING, ParameterType::INTEGER)));

        return $this->returnFirstOrNull($qb);
    }

    public function findNextPendingDataflow(): ?Job
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->lte('requested_date', $qb->createNamedParameter(new \DateTime(), 'datetime')))
            ->andWhere($qb->expr()->eq('status', $qb->createNamedParameter(Job::STATUS_PENDING, ParameterType::INTEGER)))
            ->orderBy('requested_date', 'ASC')
            ->setMaxResults(1)
        ;

        return $this->returnFirstOrNull($qb);
    }

    public function findLastForDataflowId(int $dataflowId): ?Job
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq('scheduled_dataflow_id', $qb->createNamedParameter($dataflowId, ParameterType::INTEGER)))
            ->orderBy('requested_date', 'DESC')
            ->setMaxResults(1)
        ;

        return $this->returnFirstOrNull($qb);
    }

    public function findLatests(): iterable
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->orderBy('requested_date', 'DESC')
            ->setMaxResults(20);
        $stmt = $qb->executeQuery();
        if (0 === $stmt->rowCount()) {
            return [];
        }
        while (false !== ($row = $stmt->fetchAssociative())) {
            yield Job::createFromArray($row);
        }
    }

    public function findForScheduled(int $id): iterable
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq('scheduled_dataflow_id', $qb->createNamedParameter($id, ParameterType::INTEGER)))
            ->orderBy('requested_date', 'DESC')
            ->setMaxResults(20);
        $stmt = $qb->executeQuery();
        if (0 === $stmt->rowCount()) {
            return [];
        }
        while (false !== ($row = $stmt->fetchAssociative())) {
            yield Job::createFromArray($row);
        }
    }

    public function save(Job $job)
    {
        $datas = $job->toArray();
        unset($datas['id']);

        if (is_array($datas['options'])) {
            $datas['options'] = json_encode($datas['options'], JSON_THROW_ON_ERROR);
        }
        if (is_array($datas['exceptions'])) {
            $datas['exceptions'] = json_encode($datas['exceptions'], JSON_THROW_ON_ERROR);
        }

        if (null === $job->getId()) {
            $this->connection->insert(static::TABLE_NAME, $datas, static::FIELDS_TYPE);
            $job->setId((int) $this->connection->lastInsertId());

            return;
        }
        $this->connection->update(static::TABLE_NAME, $datas, ['id' => $job->getId()], static::FIELDS_TYPE);
    }

    public function createQueryBuilder($alias = null): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from(static::TABLE_NAME, $alias);

        return $qb;
    }

    private function returnFirstOrNull(QueryBuilder $qb): ?Job
    {
        $stmt = $qb->executeQuery();
        if (0 === $stmt->rowCount()) {
            return null;
        }

        return Job::createFromArray($this->initDateTime($this->initArray($stmt->fetchAssociative())));
    }
}
