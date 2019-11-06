<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Entity;

use Symfony\Component\Validator\Constraints as Asserts;

/**
 * Dataflow execution status.
 *
 * @codeCoverageIgnore
 */
class Job
{
    const STATUS_PENDING = 0;
    const STATUS_RUNNING = 1;
    const STATUS_COMPLETED = 2;

    /**
     * @var null|int
     */
    private $id;

    /**
     * @var int
     *
     * @Asserts\Range(min=0, max=2)
     */
    private $status;

    /**
     * @var string|null
     *
     * @Asserts\NotBlank()
     * @Asserts\Length(min=1, max=255)
     * @Asserts\Regex("#^[[:alnum:] ]+\z#u")
     */
    private $label;

    /**
     * @var string|null
     *
     * @Asserts\NotBlank()
     * @Asserts\Length(min=1, max=255)
     * @Asserts\Regex("#^[\w\\]+\z#u")
     */
    private $dataflowType;

    /**
     * @var array|null
     */
    private $options;

    /**
     * @var \DateTimeInterface|null
     *
     * @Asserts\DateTime()
     */
    private $requestedDate;

    /**
     * @var int|null
     */
    private $scheduledDataflowId;

    /**
     * @var int|null
     */
    private $count;

    /**
     * @var array|null
     */
    private $exceptions;

    /**
     * @var \DateTimeInterface|null
     */
    private $startTime;

    /**
     * @var \DateTimeInterface|null
     */
    private $endTime;

    /**
     * @param ScheduledDataflow $scheduled
     *
     * @return Job
     */
    public static function createFromScheduledDataflow(ScheduledDataflow $scheduled): self
    {
        return (new static())
            ->setStatus(static::STATUS_PENDING)
            ->setDataflowType($scheduled->getDataflowType())
            ->setOptions($scheduled->getOptions())
            ->setRequestedDate(clone $scheduled->getNext())
            ->setLabel($scheduled->getLabel())
            ->setScheduledDataflowId($scheduled->getId());
    }

    public function __construct()
    {
        $this->count = 0;
        $this->status = static::STATUS_PENDING;
    }

    public static function createFromArray(array $datas)
    {
        $keys = [
            'id',
            'status',
            'label',
            'dataflow_type',
            'options',
            'requested_date',
            'scheduled_dataflow_id',
            'count',
            'exceptions',
            'start_time',
            'end_time',
        ];
        $lost = array_diff($keys, array_keys($datas));
        if (count($lost) > 0) {
            throw new \LogicException('The first argument of ' . __METHOD__ . '  must be contains: "' . implode(', ',
                    $lost) . '"');
        }

        $job = new self();
        $job->id = $datas['id'] === null ? null : (int)$datas['id'];
        $job->setStatus($datas['status'] === null ? null : (int)$datas['status']);
        $job->setLabel($datas['label']);
        $job->setDataflowType($datas['dataflow_type']);
        $job->setOptions($datas['options']);
        $job->setRequestedDate($datas['requested_date']);
        $job->setScheduledDataflowId($datas['scheduled_dataflow_id'] === null ? null : (int)$datas['scheduled_dataflow_id']);
        $job->setCount($datas['count'] === null ? null : (int)$datas['count']);
        $job->setExceptions($datas['exceptions']);
        $job->setStartTime($datas['start_time']);
        $job->setEndTime($datas['end_time']);
        return $job;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'status' => $this->getStatus(),
            'label' => $this->getLabel(),
            'dataflow_type' => $this->getDataflowType(),
            'options' => $this->getOptions(),
            'requested_date' => $this->getRequestedDate(),
            'scheduled_dataflow_id' => $this->getScheduledDataflowId(),
            'count' => $this->getCount(),
            'exceptions' => $this->getExceptions(),
            'start_time' => $this->getStartTime(),
            'end_time' => $this->getEndTime(),
        ];
    }

    /**
     * @param int $id
     * @return Job
     */
    public function setId(int $id): Job
    {
        $this->id = $id;

        return $this;
    }


    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return Job
     */
    public function setStatus(int $status): Job
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     *
     * @return Job
     */
    public function setLabel(?string $label): Job
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDataflowType(): ?string
    {
        return $this->dataflowType;
    }

    /**
     * @param string|null $dataflowType
     *
     * @return Job
     */
    public function setDataflowType(?string $dataflowType): Job
    {
        $this->dataflowType = $dataflowType;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param array|null $options
     *
     * @return Job
     */
    public function setOptions(?array $options): Job
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getRequestedDate(): ?\DateTimeInterface
    {
        return $this->requestedDate;
    }

    /**
     * @param \DateTimeInterface|null $requestedDate
     *
     * @return Job
     */
    public function setRequestedDate(?\DateTimeInterface $requestedDate): Job
    {
        $this->requestedDate = $requestedDate;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getScheduledDataflowId(): ?int
    {
        return $this->scheduledDataflowId;
    }

    /**
     * @param int|null $scheduledDataflowId
     *
     * @return Job
     */
    public function setScheduledDataflowId(?int $scheduledDataflowId): Job
    {
        $this->scheduledDataflowId = $scheduledDataflowId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * @param int|null $count
     *
     * @return Job
     */
    public function setCount(?int $count): Job
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getExceptions(): ?array
    {
        return $this->exceptions;
    }

    /**
     * @param array|null $exceptions
     *
     * @return Job
     */
    public function setExceptions(?array $exceptions): Job
    {
        $this->exceptions = $exceptions;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    /**
     * @param \DateTimeInterface|null $startTime
     *
     * @return Job
     */
    public function setStartTime(?\DateTimeInterface $startTime): Job
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    /**
     * @param \DateTimeInterface|null $endTime
     *
     * @return Job
     */
    public function setEndTime(?\DateTimeInterface $endTime): Job
    {
        $this->endTime = $endTime;

        return $this;
    }
}
