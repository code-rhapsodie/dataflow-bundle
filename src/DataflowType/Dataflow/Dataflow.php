<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Dataflow;

use CodeRhapsodie\DataflowBundle\DataflowType\Result;
use CodeRhapsodie\DataflowBundle\DataflowType\Writer\WriterInterface;

class Dataflow implements DataflowInterface
{
    /** @var string */
    private $name;

    /** @var iterable */
    private $reader;

    /** @var callable[] */
    private $steps = [];

    /** @var WriterInterface[] */
    private $writers = [];

    /**
     * @param iterable    $reader
     * @param string|null $name
     */
    public function __construct(iterable $reader, ?string $name)
    {
        $this->reader = $reader;
        $this->name = $name;
    }

    /**
     * @param callable $step
     *
     * @return $this
     */
    public function addStep(callable $step): self
    {
        $this->steps[] = $step;

        return $this;
    }

    /**
     * @param WriterInterface $writer
     *
     * @return $this
     */
    public function addWriter(WriterInterface $writer): self
    {
        $this->writers[] = $writer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process(): Result
    {
        $count = 0;
        $exceptions = [];
        $startTime = new \DateTime();

        foreach ($this->writers as $writer) {
            $writer->prepare();
        }

        foreach ($this->reader as $index => $item) {
            try {
                $this->processItem($item);
            } catch (\Exception $e) {
                $exceptions[$index] = $e;
            }

            ++$count;
        }

        foreach ($this->writers as $writer) {
            $writer->finish();
        }

        return new Result($this->name, $startTime, new \DateTime(), $count, $exceptions);
    }

    /**
     * @param mixed $item
     */
    private function processItem($item): void
    {
        foreach ($this->steps as $step) {
            $item = call_user_func($step, $item);

            if (false === $item) {
                return;
            }
        }

        foreach ($this->writers as $writer) {
            $writer->write($item);
        }
    }
}
