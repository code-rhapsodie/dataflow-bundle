<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\ExceptionsHandler;

class NullExceptionHandler implements ExceptionHandlerInterface
{
    public function save(?int $jobId, ?array $exceptions): void
    {
        // Nothing to do
    }

    public function find(int $jobId): ?array
    {
        return null;
    }

    public function delete(int $jobId): void
    {
        // Nothing to do
    }
}
