<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Entity;

use CodeRhapsodie\DataflowBundle\Validator\Constraints\Frequency;
use Doctrine\ORM\Mapping as ORM;

/**
 * Schedule for a regular execution of a dataflow.
 *
 * @ORM\Entity(repositoryClass="CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository")
 * @ORM\Table(name="cr_dataflow_scheduled")
 *
 * @codeCoverageIgnore
 */
class ScheduledDataflow
{
    const AVAILABLE_FREQUENCIES = [
        '1 hour',
        '1 day',
        '1 week',
        '1 month',
    ];

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @var string|null
     *
     * @ORM\Column(type="string")
     *
     * @Frequency()
     */
    private $frequency;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $next;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @var Job[]
     *
     * @ORM\OneToMany(targetEntity="Job", mappedBy="scheduledDataflow", cascade={"persist", "remove"})
     * @ORM\OrderBy({"startTime" = "DESC"})
     */
    private $jobs;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return ScheduledDataflow
     */
    public function setLabel(?string $label): ScheduledDataflow
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
     * @return ScheduledDataflow
     */
    public function setDataflowType(?string $dataflowType): ScheduledDataflow
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
     * @return ScheduledDataflow
     */
    public function setOptions(?array $options): ScheduledDataflow
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    /**
     * @param string|null $frequency
     *
     * @return ScheduledDataflow
     */
    public function setFrequency(?string $frequency): ScheduledDataflow
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getNext(): ?\DateTimeInterface
    {
        return $this->next;
    }

    /**
     * @param \DateTimeInterface|null $next
     *
     * @return ScheduledDataflow
     */
    public function setNext(?\DateTimeInterface $next): ScheduledDataflow
    {
        $this->next = $next;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param bool|null $enabled
     *
     * @return ScheduledDataflow
     */
    public function setEnabled(?bool $enabled): ScheduledDataflow
    {
        $this->enabled = $enabled;

        return $this;
    }
}
