<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\ExceptionsHandler;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;

class FilesystemExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(private readonly Filesystem $filesystem)
    {
    }

    public function save(?int $jobId, $exceptions): void
    {
        if ($jobId === null || !\is_resource($exceptions) || stream_get_contents($exceptions, 1) === false) {
            return;
        }

        $path = \sprintf('dataflow-job-%s.log', $jobId);
        rewind($exceptions);

        if ($this->filesystem->fileExists($path)) {
            $existingStream = $this->filesystem->readStream($path);

            $combined = fopen('php://temp', 'r+');

            stream_copy_to_stream($existingStream, $combined);
            stream_copy_to_stream($exceptions, $combined);

            rewind($combined);

            $this->filesystem->delete($path);
            $this->filesystem->writeStream($path, $combined);

            fclose($existingStream);
            fclose($combined);

            return;
        }

        $this->filesystem->writeStream($path, $exceptions);

        fclose($exceptions);
    }

    public function find(int $jobId)
    {
        try {
            if (!$this->filesystem->fileExists(\sprintf('dataflow-job-%s.log', $jobId))) {
                return null;
            }

            return $this->filesystem->readStream(\sprintf('dataflow-job-%s.log', $jobId));
        } catch (FilesystemException) {
            return null;
        }
    }

    public function delete(int $jobId): void
    {
        $this->filesystem->delete(\sprintf('dataflow-job-%s.log', $jobId));
    }
}
