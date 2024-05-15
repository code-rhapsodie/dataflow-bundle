<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Repository;

use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Repository for the ScheduledDataflow entity.
 *
 * @codeCoverageIgnore
 */
class ScheduledDataflowRepository
{
    use InitFromDbTrait;

    public const TABLE_NAME = 'cr_dataflow_scheduled';

    /**
     * JobRepository constructor.
     */
    public function __construct(private Connection $connection)
    {
    }

    /**
     * Finds all enabled scheduled dataflows with a passed next run date.
     *
     * @return ScheduledDataflow[]
     */
    public function findReadyToRun(): iterable
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->lte('next', $qb->createNamedParameter(new \DateTime(), 'datetime')))
            ->andWhere($qb->expr()->eq('enabled', 1))
            ->orderBy('next', 'ASC')
        ;

        $stmt = $qb->executeQuery();
        if (0 === $stmt->rowCount()) {
            return [];
        }
        while (false !== ($row = $stmt->fetchAssociative())) {
            yield ScheduledDataflow::createFromArray($this->initDateTime($this->initArray($row)));
        }
    }

    public function find(int $scheduleId): ?ScheduledDataflow
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq('id', $qb->createNamedParameter($scheduleId, ParameterType::INTEGER)))
            ->setMaxResults(1)
        ;

        return $this->returnFirstOrNull($qb);
    }

    public function findAllOrderedByLabel(): iterable
    {
        $qb = $this->createQueryBuilder();
        $qb->orderBy('label', 'ASC');

        $stmt = $qb->executeQuery();
        if (0 === $stmt->rowCount()) {
            return [];
        }
        while (false !== ($row = $stmt->fetchAssociative())) {
            yield ScheduledDataflow::createFromArray($this->initDateTime($this->initArray($row)));
        }
    }

    public function listAllOrderedByLabel(): array
    {
        $query = $this->connection->createQueryBuilder()
            ->from(static::TABLE_NAME, 'w')
            ->select('w.id', 'w.label', 'w.enabled', 'w.next', 'max(j.start_time) as startTime')
            ->leftJoin('w', JobRepository::TABLE_NAME, 'j', 'j.scheduled_dataflow_id = w.id')
            ->orderBy('w.label', 'ASC')
            ->groupBy('w.id');

        return $query->executeQuery()->fetchAllAssociative();
    }

    public function save(ScheduledDataflow $scheduledDataflow)
    {
        $datas = $scheduledDataflow->toArray();
        unset($datas['id']);

        if (is_array($datas['options'])) {
            $datas['options'] = json_encode($datas['options'], JSON_THROW_ON_ERROR);
        }

        if (null === $scheduledDataflow->getId()) {
            $this->connection->insert(static::TABLE_NAME, $datas, $this->getFields());
            $scheduledDataflow->setId((int) $this->connection->lastInsertId());

            return;
        }
        $this->connection->update(static::TABLE_NAME, $datas, ['id' => $scheduledDataflow->getId()], $this->getFields());
    }

    public function delete(int $id): void
    {
        $this->connection->beginTransaction();
        try {
            $this->connection->delete(JobRepository::TABLE_NAME, ['scheduled_dataflow_id' => $id]);
            $this->connection->delete(static::TABLE_NAME, ['id' => $id]);
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $this->connection->commit();
    }

    public function createQueryBuilder($alias = null): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from(static::TABLE_NAME, $alias);

        return $qb;
    }

    private function returnFirstOrNull(QueryBuilder $qb): ?ScheduledDataflow
    {
        $stmt = $qb->executeQuery();
        if (0 === $stmt->rowCount()) {
            return null;
        }

        return ScheduledDataflow::createFromArray($this->initDateTime($this->initArray($stmt->fetchAssociative())));
    }

    private function getFields(): array
    {
        return [
            'id' => ParameterType::INTEGER,
            'label' => ParameterType::STRING,
            'dataflow_type' => ParameterType::STRING,
            'options' => ParameterType::STRING,
            'frequency' => ParameterType::STRING,
            'next' => 'datetime',
            'enabled' => ParameterType::BOOLEAN,
        ];
    }
}
