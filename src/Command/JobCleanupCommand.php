<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'code-rhapsodie:job:cleanup', description: 'Cleanup job history.')]
class JobCleanupCommand extends Command
{
    public function __construct(private JobRepository $jobRepository, private int $retention)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setHelp('Job retention can be configured with the "job_history.retention" configuration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->jobRepository->deleteOld($this->retention);

        return Command::SUCCESS;
    }
}
