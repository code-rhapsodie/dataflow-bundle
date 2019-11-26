<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType;

/**
 * @codeCoverageIgnore
 */
class Result
{
    /** @var string */
    private $name;

    /** @var \DateTimeInterface */
    private $startTime;

    /** @var \DateTimeInterface */
    private $endTime;

    /** @var \DateInterval */
    private $elapsed;

    /** @var int */
    private $errorCount = 0;

    /** @var int */
    private $successCount = 0;

    /** @var int */
    private $totalProcessedCount = 0;

    /** @var array */
    private $exceptions;

    public function __construct(string $name, \DateTimeInterface $startTime, \DateTimeInterface $endTime, int $totalCount, array $exceptions)
    {
        $this->name = $name;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->elapsed = $startTime->diff($endTime);
        $this->totalProcessedCount = $totalCount;
        $this->errorCount = count($exceptions);
        $this->successCount = $totalCount - $this->errorCount;
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
