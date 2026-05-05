<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\ExceptionsHandler;

class NullExceptionHandler implements ExceptionHandlerInterface
{
    /** @inheritDoc */
    public function save(?int $jobId, $exceptions): void
    {
        // Nothing to do
    }

    /** @inheritDoc */
    public function find(int $jobId)
    {
        return null;
    }

    public function delete(int $jobId): void
    {
        // Nothing to do
    }
}
