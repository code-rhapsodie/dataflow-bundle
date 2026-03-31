<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\ExceptionsHandler\ExceptionHandlerInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'code-rhapsodie:dataflow:job:cleanup', description: 'Cleanup job history.', help: <<<'TXT'
Job retention can be configured with the "job_history.retention" configuration.
TXT)]
final readonly class JobCleanupCommand
{
    public function __construct(
        private JobRepository $jobRepository,
        private ExceptionHandlerInterface $exceptionHandler,
        private int $retention,
    ) {
    }

    public function __invoke(): int
    {
        $removedIds = $this->jobRepository->deleteOld($this->retention);
        foreach ($removedIds as $jobId) {
            $this->exceptionHandler->delete($jobId);
        }

        return Command::SUCCESS;
    }
}
