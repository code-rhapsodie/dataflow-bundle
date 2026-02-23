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
    public const STATUS_PENDING = 0;
    public const STATUS_RUNNING = 1;
    public const STATUS_COMPLETED = 2;
    public const STATUS_QUEUED = 3;
    public const STATUS_CRASHED = 4;

    private const KEYS = [
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

    private ?int $id = null;

    #[Asserts\Range(min: 0, max: 2)]
    private int $status = self::STATUS_PENDING;

    #[Asserts\NotBlank]
    #[Asserts\Length(min: 1, max: 255)]
    #[Asserts\Regex('#^[[:alnum:] ]+\z#u')]
    private ?string $label = null;

    #[Asserts\NotBlank]
    #[Asserts\Length(min: 1, max: 255)]
    #[Asserts\Regex('#^[[:alnum:]\\\]+\z#u')]
    private ?string $dataflowType = null;

    private ?array $options = null;

    private ?\DateTimeInterface $requestedDate = null;

    private ?int $scheduledDataflowId = null;

    private ?int $count = 0;

    private ?array $exceptions = null;

    private ?\DateTimeInterface $startTime = null;

    private ?\DateTimeInterface $endTime = null;

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

    public static function createFromArray(array $datas)
    {
        $lost = array_diff(static::KEYS, array_keys($datas));
        if (\count($lost) > 0) {
            throw new \LogicException('The first argument of '.__METHOD__.'  must be contains: "'.implode(', ', $lost).'"');
        }

        $job = new self();
        $job->id = $datas['id'] === null ? null : (int) $datas['id'];
        $job->setStatus($datas['status'] === null ? null : (int) $datas['status']);
        $job->setLabel($datas['label']);
        $job->setDataflowType($datas['dataflow_type']);
        $job->setOptions($datas['options']);
        $job->setRequestedDate($datas['requested_date']);
        $job->setScheduledDataflowId($datas['scheduled_dataflow_id'] === null ? null : (int) $datas['scheduled_dataflow_id']);
        $job->setCount($datas['count'] === null ? null : (int) $datas['count']);
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

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getDataflowType(): ?string
    {
        return $this->dataflowType;
    }

    public function setDataflowType(?string $dataflowType): self
    {
        $this->dataflowType = $dataflowType;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getRequestedDate(): ?\DateTimeInterface
    {
        return $this->requestedDate;
    }

    public function setRequestedDate(?\DateTimeInterface $requestedDate): self
    {
        $this->requestedDate = $requestedDate;

        return $this;
    }

    public function getScheduledDataflowId(): ?int
    {
        return $this->scheduledDataflowId;
    }

    public function setScheduledDataflowId(?int $scheduledDataflowId): self
    {
        $this->scheduledDataflowId = $scheduledDataflowId;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(?int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getExceptions(): ?array
    {
        return $this->exceptions;
    }

    public function setExceptions(?array $exceptions): self
    {
        $this->exceptions = $exceptions;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }
}
