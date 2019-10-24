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

    /**
     * @param string             $name
     * @param \DateTimeInterface $startTime
     * @param \DateTimeInterface $endTime
     * @param int                $totalCount
     * @param \SplObjectStorage  $exceptions
     */
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getStartTime(): \DateTimeInterface
    {
        return $this->startTime;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEndTime(): \DateTimeInterface
    {
        return $this->endTime;
    }

    /**
     * @return \DateInterval
     */
    public function getElapsed(): \DateInterval
    {
        return $this->elapsed;
    }

    /**
     * @return int
     */
    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    /**
     * @return int
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * @return int
     */
    public function getTotalProcessedCount(): int
    {
        return $this->totalProcessedCount;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->errorCount > 0;
    }

    /**
     * @return array
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
