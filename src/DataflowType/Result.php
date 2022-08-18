<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType;

/**
 * @codeCoverageIgnore
 */
class Result
{
    private \DateInterval $elapsed;

    private int $errorCount = 0;

    private int $successCount = 0;

    private array $exceptions;

    public function __construct(private string $name, private \DateTimeInterface $startTime, private \DateTimeInterface $endTime, private int $totalProcessedCount, array $exceptions)
    {
        $this->elapsed = $startTime->diff($endTime);
        $this->errorCount = count($exceptions);
        $this->successCount = $totalProcessedCount - $this->errorCount;
        $this->exceptions = $exceptions;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStartTime(): \DateTimeInterface
    {
        return $this->startTime;
    }

    public function getEndTime(): \DateTimeInterface
    {
        return $this->endTime;
    }

    public function getElapsed(): \DateInterval
    {
        return $this->elapsed;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getTotalProcessedCount(): int
    {
        return $this->totalProcessedCount;
    }

    public function hasErrors(): bool
    {
        return $this->errorCount > 0;
    }

    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
