<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType;

use CodeRhapsodie\DataflowBundle\DataflowType\Dataflow\AMPAsyncDataflow;
use CodeRhapsodie\DataflowBundle\DataflowType\Dataflow\DataflowInterface;
use CodeRhapsodie\DataflowBundle\DataflowType\Writer\WriterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AMPAsyncDataflowBuilder extends DataflowBuilder
{
    /** @var int  */
    protected $loopInterval;

    /** @var int */
    protected $emitInterval;

    public function __construct(?int $loopInterval = 0, ?int $emitInterval = 0)
    {
        $this->loopInterval = $loopInterval;
        $this->emitInterval = $emitInterval;
    }

    /** @var string */
    private $name;

    /** @var iterable */
    private $reader;

    /** @var array */
    private $steps = [];

    /** @var WriterInterface[] */
    private $writers = [];

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setReader(iterable $reader): self
    {
        $this->reader = $reader;

        return $this;
    }

    public function addStep(callable $step, int $priority = 0, int $scale = 1): self
    {
        $this->steps[$priority][] = ['step' => $step, 'scale' => $scale];

        return $this;
    }

    public function addWriter(WriterInterface $writer): self
    {
        $this->writers[] = $writer;

        return $this;
    }

    public function getDataflow(): DataflowInterface
    {
        $dataflow = new AMPAsyncDataflow($this->reader, $this->name, $this->loopInterval, $this->emitInterval);

        krsort($this->steps);
        foreach ($this->steps as $stepArray) {
            foreach ($stepArray as $step) {
                $dataflow->addStep($step['step'], $step['scale']);
            }
        }

        foreach ($this->writers as $writer) {
            $dataflow->addWriter($writer);
        }

        return $dataflow;
    }
}
