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
    const AVAILABLE_FREQUENCIES = [
        '1 hour',
        '1 day',
        '1 week',
        '1 month',
    ];

    /**
     * @var null|int
     */
    private $id;

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
     * @var string|null
     *
     * @Asserts\NotBlank()
     * @Frequency()
     */
    private $frequency;

    /**
     * @var \DateTimeInterface|null
     */
    private $next;

    /**
     * @var bool|null
     */
    private $enabled;

    public static function createFromArray(array $datas)
    {
        $keys = ['id', 'label', 'dataflow_type', 'options', 'frequency', 'next', 'enabled',];
        $lost = array_diff($keys, array_keys($datas));
        if (count($lost) > 0) {
            throw new \LogicException('The first argument of ' . __METHOD__ . '  must be contains: "' . implode(', ',
                    $lost) . '"');
        }

        $scheduledDataflow = new self();
        $scheduledDataflow->id = $datas['id'] === null ? null : (int)$datas['id'];

        $scheduledDataflow->setLabel($datas['label']);
        $scheduledDataflow->setDataflowType($datas['dataflow_type']);
        $scheduledDataflow->setOptions($datas['options']);
        $scheduledDataflow->setFrequency($datas['frequency']);
        $scheduledDataflow->setNext($datas['next']);
        $scheduledDataflow->setEnabled($datas['enabled'] === null ? null : (bool)$datas['enabled']);
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

    /**
     * @param int $id
     *
     * @return ScheduledDataflow
     */
    public function setId(int $id): ScheduledDataflow
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
