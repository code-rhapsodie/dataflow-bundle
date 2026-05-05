<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Dataflow;

use CodeRhapsodie\DataflowBundle\DataflowType\Result;
use CodeRhapsodie\DataflowBundle\DataflowType\Writer\WriterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Dataflow implements DataflowInterface
{
    use LoggerAwareTrait;

    /** @var callable[] */
    private array $steps = [];

    /** @var WriterInterface[] */
    private array $writers = [];

    private ?\Closure $customExceptionIndex = null;

    /**
     * @var \Closure[]
     */
    private array $afterItemProcessors = [];

    public function __construct(private iterable $reader, private ?string $name)
    {
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
     * @return $this
     */
    public function setCustomExceptionIndex(callable $callable): self
    {
        $this->customExceptionIndex = \Closure::fromCallable($callable);

        return $this;
    }

    /**
     * @param array<callable> $processors
     */
    public function setAfterItemProcessors(array $processors): self
    {
        $this->afterItemProcessors = array_map(\Closure::fromCallable(...), $processors);

        return $this;
    }

    public function process(): Result
    {
        $count = 0;
        $countExceptions = 0;
        $startTime = new \DateTime();

        try {
            foreach ($this->writers as $writer) {
                $writer->prepare();
            }

            foreach ($this->reader as $index => $item) {
                try {
                    $this->processItem($item);
                } catch (\Throwable $e) {
                    $exceptionIndex = $index;
                    try {
                        if (\is_callable($this->customExceptionIndex)) {
                            $exceptionIndex = (string) ($this->customExceptionIndex)($item, $index);
                        }
                    } catch (\Throwable $e2) {
                        ++$countExceptions;
                        $this->logException($e2, $index);
                    }
                    ++$countExceptions;
                    $this->logException($e, $exceptionIndex);
                }

                ++$count;

                foreach ($this->afterItemProcessors as $afterItemProcessor) {
                    $afterItemProcessor($index, $item, $count);
                }
            }

            foreach ($this->writers as $writer) {
                $writer->finish();
            }
        } catch (\Throwable $e) {
            ++$countExceptions;
            $this->logException($e);
        }

        return new Result($this->name, $startTime, new \DateTime(), $count, $countExceptions);
    }

    private function processItem(mixed $item): void
    {
        foreach ($this->steps as $step) {
            $item = \call_user_func($step, $item);

            if ($item === false) {
                return;
            }
        }

        foreach ($this->writers as $writer) {
            $writer->write($item);
        }
    }

    private function logException(\Throwable $e, string|int|null $index = null): void
    {
        if (!isset($this->logger)) {
            return;
        }

        $this->logger->error($e, ['exception' => $e, 'index' => $index]);
    }
}
