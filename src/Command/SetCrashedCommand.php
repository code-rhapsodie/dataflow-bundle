<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'code-rhapsodie:dataflow:job:set-crashed', description: 'Set long running jobs as crashed.', help: <<<'TXT'
How long jobs have to run before they are set as crashed can be configured with the "job_history.crashed_delay" configuration.
TXT)]
final readonly class SetCrashedCommand
{
    public function __construct(private JobRepository $jobRepository, private int $crashedDelay)
    {
    }

    public function __invoke(): int
    {
        $this->jobRepository->crashLongRunning($this->crashedDelay);

        return Command::SUCCESS;
    }
}
