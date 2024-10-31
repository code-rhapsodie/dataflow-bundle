<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Dataflow;

use function Amp\coroutine;
use Amp\Deferred;
use Amp\Delayed;
use Amp\Loop;
use Amp\Producer;
use Amp\Promise;
use function Amp\Promise\wait;
use CodeRhapsodie\DataflowBundle\DataflowType\Result;
use CodeRhapsodie\DataflowBundle\DataflowType\Writer\WriterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Throwable;

class AMPAsyncDataflow implements DataflowInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var callable[] */
    private array $steps = [];

    /** @var WriterInterface[] */
    private array $writers = [];

    private array $states = [];

    private array $stepsJobs = [];

    public function __construct(private iterable $reader, private ?string $name, private ?int $loopInterval = 0, private ?int $emitInterval = 0)
    {
        if (!function_exists('Amp\\Promise\\wait')) {
            throw new RuntimeException('Amp is not loaded. Suggest install it with composer require amphp/amp');
        }
    }

    /**
     * @param int $scale
     *
     * @return $this
     */
    public function addStep(callable $step, $scale = 1): self
    {
        $this->steps[] = [$step, $scale];

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
        $startTime = new \DateTime();

        try {
            foreach ($this->writers as $writer) {
                $writer->prepare();
            }

            $deferred = new Deferred();
            $resolved = false; //missing $deferred->isResolved() in version 2.5
            $producer = new Producer(function (callable $emit) {
                foreach ($this->reader as $index => $item) {
                    yield new Delayed($this->emitInterval);
                    yield $emit([$index, $item]);
                }
            });

            $watcherId = Loop::repeat($this->loopInterval, function () use ($deferred, &$resolved, $producer, &$count, &$exceptions) {
                if (yield $producer->advance()) {
                    $it = $producer->getCurrent();
                    [$index, $item] = $it;
                    $this->states[$index] = [$index, 0, $item];
                } elseif (!$resolved && 0 === count($this->states)) {
                    $resolved = true;
                    $deferred->resolve();
                }

                foreach ($this->states as $state) {
                    $this->processState($state, $count, $exceptions);
                }
            });

            wait($deferred->promise());
            Loop::cancel($watcherId);

            foreach ($this->writers as $writer) {
                $writer->finish();
            }
        } catch (\Throwable $e) {
            $exceptions[] = $e;
            $this->logException($e);
        }

        return new Result($this->name, $startTime, new \DateTime(), $count, $exceptions);
    }

    /**
     * @param int   $count      internal count reference
     * @param array $exceptions internal exceptions
     */
    private function processState(mixed $state, int &$count, array &$exceptions): void
    {
        [$readIndex, $stepIndex, $item] = $state;
        if ($stepIndex < count($this->steps)) {
            if (!isset($this->stepsJobs[$stepIndex])) {
                $this->stepsJobs[$stepIndex] = [];
            }
            [$step, $scale] = $this->steps[$stepIndex];
            if ((is_countable($this->stepsJobs[$stepIndex]) ? count($this->stepsJobs[$stepIndex]) : 0) < $scale && !isset($this->stepsJobs[$stepIndex][$readIndex])) {
                $this->stepsJobs[$stepIndex][$readIndex] = true;
                /** @var Promise<void> $promise */
                $promise = coroutine($step)($item);
                $promise->onResolve(function (?Throwable $exception = null, $newItem = null) use ($stepIndex, $readIndex, &$exceptions) {
                    if ($exception) {
                        $exceptions[$stepIndex] = $exception;
                        $this->logException($exception, (string) $stepIndex);
                    } elseif (false === $newItem) {
                        unset($this->states[$readIndex]);
                    } else {
                        $this->states[$readIndex] = [$readIndex, $stepIndex + 1, $newItem];
                    }

                    unset($this->stepsJobs[$stepIndex][$readIndex]);
                });
            }
        } else {
            unset($this->states[$readIndex]);

            foreach ($this->writers as $writer) {
                $writer->write($item);
            }

            ++$count;
        }
    }

    private function logException(Throwable $e, ?string $index = null): void
    {
        if (!isset($this->logger)) {
            return;
        }

        $this->logger->error($e, ['exception' => $e, 'index' => $index]);
    }
}
