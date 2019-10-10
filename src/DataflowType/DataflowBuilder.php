<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType;

use CodeRhapsodie\DataflowBundle\DataflowType\Dataflow\Dataflow;
use CodeRhapsodie\DataflowBundle\DataflowType\Dataflow\DataflowInterface;
use CodeRhapsodie\DataflowBundle\DataflowType\Writer\WriterInterface;

class DataflowBuilder
{
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

    public function addStep(callable $step, int $priority = 0): self
    {
        $this->steps[$priority][] = $step;

        return $this;
    }

    public function addWriter(WriterInterface $writer): self
    {
        $this->writers[] = $writer;

        return $this;
    }

    public function getDataflow(): DataflowInterface
    {
        $dataflow = new Dataflow($this->reader, $this->name);

        krsort($this->steps);
        foreach ($this->steps as $stepArray) {
            foreach ($stepArray as $step) {
                $dataflow->addStep($step);
            }
        }

        foreach ($this->writers as $writer) {
            $dataflow->addWriter($writer);
        }

        return $dataflow;
    }
}
