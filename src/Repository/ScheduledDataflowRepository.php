<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Repository;

use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use Doctrine\DBAL\Driver\Connection;
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

    private const FIELDS_TYPE = [
        'id' => \PDO::PARAM_INT,
        'label' => \PDO::PARAM_STR,
        'dataflow_type' => \PDO::PARAM_STR,
        'options' => \PDO::PARAM_STR,
        'frequency' => \PDO::PARAM_STR,
        'next' => 'datetime',
        'enabled' => \PDO::PARAM_BOOL,
    ];
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connexion;

    /**
     * JobRepository constructor.
     *
     * @param Connection $connexion
     */
    public function __construct(Connection $connexion)
    {
        $this->connexion = $connexion;
    }

    /**
     * Finds all enabled scheduled dataflows with a passed next run date.
     *
     * @return ScheduledDataflow[]
     */
    public function findReadyToRun(): iterable
    {
        $qb = $this->getQueryBuilder();
        $qb->andWhere($qb->expr()->lte('next', $qb->createNamedParameter(new \DateTime(), 'datetime')))
            ->andWhere($qb->expr()->eq('enabled', 1))
            ->orderBy('next', 'ASC')
            ;

        $stmt = $qb->execute();
        if (0 === $stmt->rowCount()) {
            return [];
        }
        while (false !== ($row = $stmt->fetch(\PDO::FETCH_ASSOC))) {
            yield ScheduledDataflow::createFromArray($this->initDateTime($this->initArray($row)));
        }
    }

    public function find(int $scheduleId): ?ScheduledDataflow
    {
        $qb = $this->getQueryBuilder();
        $qb->andWhere($qb->expr()->eq('id', $qb->createNamedParameter($scheduleId, \PDO::PARAM_INT)))
            ->setMaxResults(1)
        ;

        return $this->returnFirstOrNull($qb);
    }

    public function findAllOrderedByLabel(): iterable
    {
        $qb = $this->getQueryBuilder();
        $qb->orderBy('label', 'ASC');

        $stmt = $qb->execute();
        if (0 === $stmt->rowCount()) {
            return [];
        }
        while (false !== ($row = $stmt->fetch(\PDO::FETCH_ASSOC))) {
            yield ScheduledDataflow::createFromArray($this->initDateTime($this->initOptions($row)));
        }
    }

    public function listAllOrderedByLabel(): array
    {
        $query = $this->connexion->createQueryBuilder()
            ->from(static::TABLE_NAME, 'w')
            ->select('w.id', 'w.label', 'w.enabled', 'w.next', 'max(j.start_time) as startTime')
            ->leftJoin('w', JobRepository::TABLE_NAME, 'j', 'j.scheduled_dataflow_id = w.id')
            ->orderBy('w.label', 'ASC')
            ->groupBy('w.id');

        return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function save(ScheduledDataflow $scheduledDataflow)
    {
        $datas = $scheduledDataflow->toArray();
        unset($datas['id']);

        if (is_array($datas['options'])) {
            $datas['options'] = json_encode($datas['options']);
        }

        if (null === $scheduledDataflow->getId()) {
            $this->connexion->insert(static::TABLE_NAME, $datas, static::FIELDS_TYPE);
            $scheduledDataflow->setId((int) $this->connexion->lastInsertId());

            return;
        }
        $this->connexion->update(static::TABLE_NAME, $datas, ['id' => $scheduledDataflow->getId()], static::FIELDS_TYPE);
    }

    public function delete(int $id): void
    {
        $this->connexion->beginTransaction();
        try {
            $this->connexion->delete(JobRepository::TABLE_NAME, ['scheduled_dataflow_id' => $id]);
            $this->connexion->delete(static::TABLE_NAME, ['id' => $id]);
        } catch (\Throwable $e) {
            $this->connexion->rollBack();
            throw $e;
        }

        $this->connexion->commit();
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $qb = $this->connexion->createQueryBuilder();
        $qb->select('*')
            ->from(static::TABLE_NAME);

        return $qb;
    }

    private function returnFirstOrNull(QueryBuilder $qb): ?ScheduledDataflow
    {
        $stmt = $qb->execute();
        if (0 === $stmt->rowCount()) {
            return null;
        }

        return ScheduledDataflow::createFromArray($this->initDateTime($this->initArray($stmt->fetch(\PDO::FETCH_ASSOC))));
    }
}
