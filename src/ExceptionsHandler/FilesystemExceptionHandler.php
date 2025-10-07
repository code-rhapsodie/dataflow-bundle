<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\ExceptionsHandler;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;

class FilesystemExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function save(?int $jobId, ?array $exceptions): void
    {
        if ($jobId === null || empty($exceptions)) {
            return;
        }

        $this->filesystem->write(sprintf('dataflow-job-%s.log', $jobId), json_encode($exceptions));
    }

    public function find(int $jobId): ?array
    {
        try {
            if (!$this->filesystem->has(sprintf('dataflow-job-%s.log', $jobId))) {
                return [];
            }

            return json_decode($this->filesystem->read(sprintf('dataflow-job-%s.log', $jobId)), true);
        } catch (FilesystemException) {
            return [];
        }
    }
}