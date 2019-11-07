<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Repository;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use Doctrine\DBAL\Driver\Connection;
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
        'id' => \PDO::PARAM_INT,
        'status' => \PDO::PARAM_INT,
        'label' => \PDO::PARAM_STR,
        'dataflow_type' => \PDO::PARAM_STR,
        'options' => \PDO::PARAM_STR,
        'requested_date' => 'datetime',
        'scheduled_dataflow_id' => \PDO::PARAM_INT,
        'count' => \PDO::PARAM_INT,
        'exceptions' => \PDO::PARAM_STR,
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * JobRepository constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(int $jobId)
    {
        $qb = $this->getQueryBuilder();
        $qb
            ->andWhere($qb->expr()->eq('id', $qb->createNamedParameter($jobId, \PDO::PARAM_INT)))
        ;

        return $this->returnFirstOrNull($qb);
    }

    public function findOneshotDataflows(): iterable
    {
        $qb = $this->getQueryBuilder();
        $qb
            ->andWhere($qb->expr()->isNull('scheduled_dataflow_id'))
            ->andWhere($qb->expr()->eq('status', $qb->createNamedParameter(Job::STATUS_PENDING, \PDO::PARAM_INT)));
        $stmt = $qb->execute();
        if (0 === $stmt->rowCount()) {
            return [];
        }
        while (false !== ($row = $stmt->fetch(\PDO::FETCH_ASSOC))) {
            yield Job::createFromArray($this->initDateTime($this->initArray($row)));
        }
    }

    public function findPendingForScheduledDataflow(ScheduledDataflow $scheduled): ?Job
    {
        $qb = $this->getQueryBuilder();
        $qb
            ->andWhere($qb->expr()->eq('scheduled_dataflow_id', $qb->createNamedParameter($scheduled->getId(), \PDO::PARAM_INT)))
            ->andWhere($qb->expr()->eq('status', $qb->createNamedParameter(Job::STATUS_PENDING, \PDO::PARAM_INT)));

        return $this->returnFirstOrNull($qb);
    }

    public function findNextPendingDataflow(): ?Job
    {
        $qb = $this->getQueryBuilder();
        $qb->andWhere($qb->expr()->lte('requested_date', $qb->createNamedParameter(new \DateTime(), 'datetime')))
            ->andWhere($qb->expr()->eq('status', $qb->createNamedParameter(Job::STATUS_PENDING, \PDO::PARAM_INT)))
            ->orderBy('requested_date', 'ASC')
            ->setMaxResults(1)
        ;

        return $this->returnFirstOrNull($qb);
    }

    public function findLastForDataflowId(int $dataflowId): ?Job
    {
        $qb = $this->getQueryBuilder();
        $qb->andWhere($qb->expr()->eq('scheduled_dataflow_id', $qb->createNamedParameter($dataflowId, \PDO::PARAM_INT)))
            ->orderBy('requested_date', 'DESC')
            ->setMaxResults(1)
        ;

        return $this->returnFirstOrNull($qb);
    }

    public function findLatests(): iterable
    {
        $qb = $this->getQueryBuilder();
        $qb
            ->orderBy('requested_date', 'DESC')
            ->setMaxResults(20);
        $stmt = $qb->execute();
        if (0 === $stmt->rowCount()) {
            return [];
        }
        while (false !== ($row = $stmt->fetch(\PDO::FETCH_ASSOC))) {
            yield Job::createFromArray($row);
        }
    }

    public function findForScheduled(int $id): iterable
    {
        $qb = $this->getQueryBuilder();
        $qb->andWhere($qb->expr()->eq('scheduled_dataflow_id', $qb->createNamedParameter($id, \PDO::PARAM_INT)))
            ->orderBy('requested_date', 'DESC')
            ->setMaxResults(20);
        $stmt = $qb->execute();
        if (0 === $stmt->rowCount()) {
            return [];
        }
        while (false !== ($row = $stmt->fetch(\PDO::FETCH_ASSOC))) {
            yield Job::createFromArray($row);
        }
    }

    public function save(Job $job)
    {
        $datas = $job->toArray();
        unset($datas['id']);

        if (is_array($datas['options'])) {
            $datas['options'] = json_encode($datas['options']);
        }
        if (is_array($datas['exceptions'])) {
            $datas['exceptions'] = json_encode($datas['exceptions']);
        }

        if (null === $job->getId()) {
            $this->connection->insert(static::TABLE_NAME, $datas, static::FIELDS_TYPE);
            $job->setId((int) $this->connection->lastInsertId());

            return;
        }
        $this->connection->update(static::TABLE_NAME, $datas, ['id' => $job->getId()], static::FIELDS_TYPE);
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from(static::TABLE_NAME);

        return $qb;
    }

    private function returnFirstOrNull(QueryBuilder $qb): ?Job
    {
        $stmt = $qb->execute();
        if (0 === $stmt->rowCount()) {
            return null;
        }

        return Job::createFromArray($this->initDateTime($this->initArray($stmt->fetch(\PDO::FETCH_ASSOC))));
    }
}
