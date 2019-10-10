<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dataflow execution status.
 *
 * @ORM\Entity(repositoryClass="CodeRhapsodie\DataflowBundle\Repository\JobRepository")
 * @ORM\Table(name="cr_dataflow_job")
 */
class Job
{
    const STATUS_PENDING = 0;
    const STATUS_RUNNING = 1;
    const STATUS_COMPLETED = 2;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     */
    private $label;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     */
    private $dataflowType;

    /**
     * @var array|null
     *
     * @ORM\Column(type="json")
     */
    private $options;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $requestedDate;

    /**
     * @var ScheduledDataflow|null
     *
     * @ORM\ManyToOne(targetEntity="ScheduledDataflow", inversedBy="jobs")
     * @ORM\JoinColumn(nullable=true)
     */
    private $scheduledDataflow;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $count;

    /**
     * @var array|null
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $exceptions;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startTime;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
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
            ->setScheduledDataflow($scheduled)
        ;
    }

    /**
     * @return int
     */
    public function getId(): int
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
     * @return null|string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param null|string $label
     *
     * @return Job
     */
    public function setLabel(?string $label): Job
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDataflowType(): ?string
    {
        return $this->dataflowType;
    }

    /**
     * @param null|string $dataflowType
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
     * @return ScheduledDataflow|null
     */
    public function getScheduledDataflow(): ?ScheduledDataflow
    {
        return $this->scheduledDataflow;
    }

    /**
     * @param ScheduledDataflow|null $scheduledDataflow
     *
     * @return Job
     */
    public function setScheduledDataflow(?ScheduledDataflow $scheduledDataflow): Job
    {
        $this->scheduledDataflow = $scheduledDataflow;

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
