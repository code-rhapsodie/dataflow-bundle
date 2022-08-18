<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Entity;

use CodeRhapsodie\DataflowBundle\Validator\Constraints\Frequency;
use Symfony\Component\Validator\Constraints as Asserts;

/**
 * Schedule for a regular execution of a dataflow.
 *
 * @codeCoverageIgnore
 */
class ScheduledDataflow
{
    public const AVAILABLE_FREQUENCIES = [
        '1 hour',
        '1 day',
        '1 week',
        '1 month',
    ];

    private const KEYS = ['id', 'label', 'dataflow_type', 'options', 'frequency', 'next', 'enabled'];

    private ?int $id = null;

    #[Asserts\NotBlank]
    #[Asserts\Length(min: 1, max: 255)]
    #[Asserts\Regex('#^[[:alnum:] ]+\z#u')]
    private ?string $label = null;

    #[Asserts\NotBlank]
    #[Asserts\Length(min: 1, max: 255)]
    #[Asserts\Regex('#^[[:alnum:]\\\]+\z#u')]
    private ?string $dataflowType = null;

    private ?array $options = null;

    /**
     * @Frequency()
     */
    #[Asserts\NotBlank]
    private ?string $frequency = null;

    private ?\DateTimeInterface $next = null;

    private ?bool $enabled = null;

    public static function createFromArray(array $datas)
    {
        $lost = array_diff(static::KEYS, array_keys($datas));
        if (count($lost) > 0) {
            throw new \LogicException('The first argument of '.__METHOD__.'  must be contains: "'.implode(', ', $lost).'"');
        }

        $scheduledDataflow = new self();
        $scheduledDataflow->id = null === $datas['id'] ? null : (int) $datas['id'];

        $scheduledDataflow->setLabel($datas['label']);
        $scheduledDataflow->setDataflowType($datas['dataflow_type']);
        $scheduledDataflow->setOptions($datas['options']);
        $scheduledDataflow->setFrequency($datas['frequency']);
        $scheduledDataflow->setNext($datas['next']);
        $scheduledDataflow->setEnabled(null === $datas['enabled'] ? null : (bool) $datas['enabled']);

        return $scheduledDataflow;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'dataflow_type' => $this->getDataflowType(),
            'options' => $this->getOptions(),
            'frequency' => $this->getFrequency(),
            'next' => $this->getNext(),
            'enabled' => $this->getEnabled(),
        ];
    }

    public function setId(int $id): ScheduledDataflow
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): ScheduledDataflow
    {
        $this->label = $label;

        return $this;
    }

    public function getDataflowType(): ?string
    {
        return $this->dataflowType;
    }

    public function setDataflowType(?string $dataflowType): ScheduledDataflow
    {
        $this->dataflowType = $dataflowType;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): ScheduledDataflow
    {
        $this->options = $options;

        return $this;
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(?string $frequency): ScheduledDataflow
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getNext(): ?\DateTimeInterface
    {
        return $this->next;
    }

    public function setNext(?\DateTimeInterface $next): ScheduledDataflow
    {
        $this->next = $next;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): ScheduledDataflow
    {
        $this->enabled = $enabled;

        return $this;
    }
}
