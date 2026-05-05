<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\ExceptionsHandler;

interface ExceptionHandlerInterface
{
    /** @param resource $exceptions */
    public function save(?int $jobId, $exceptions): void;

    /** @return resource|null */
    public function find(int $jobId);

    public function delete(int $jobId): void;
}
