<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Dataflow;

use CodeRhapsodie\DataflowBundle\DataflowType\Result;
use CodeRhapsodie\DataflowBundle\DataflowType\Writer\WriterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Dataflow implements DataflowInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var string */
    private $name;

    /** @var iterable */
    private $reader;

    /** @var callable[] */
    private $steps;

    /** @var WriterInterface[] */
    private $writers;

    public function __construct(iterable $reader, ?string $name)
    {
        $this->reader = $reader;
        $this->name = $name;
        $this->steps = [];
        $this->writers = [];
    }

    /**
     * @return $this
     */
    public function addStep(callable $step): self
    {
        $this->steps[] = $step;

        return $this;
    }

    /**
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
        $startTime = new \DateTimeImmutable();

        try {
            foreach ($this->writers as $writer) {
                $writer->prepare();
            }

            foreach ($this->reader as $index => $item) {
                try {
                    $this->processItem($item);
                } catch (\Throwable $e) {
                    $exceptions[$index] = $e;
                    $this->logException($e, (string) $index);
                }

                ++$count;
            }

            foreach ($this->writers as $writer) {
                $writer->finish();
            }
        } catch (\Throwable $e) {
            $exceptions[] = $e;
            $this->logException($e);
        }

        return new Result($this->name, $startTime, new \DateTimeImmutable(), $count, $exceptions);
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

    private function logException(\Throwable $e, ?string $index = null): void
    {
        if (!isset($this->logger)) {
            return;
        }

        $this->logger->error($e, ['exception' => $e, 'index' => $index]);
    }
}
