<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'code-rhapsodie:set_crashed', description: 'Set long running jobs as crashed.')]
class SetCrashedCommand extends Command
{
    public function __construct(private JobRepository $jobRepository, private int $crashedDelay)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setHelp('How long jobs have to run before they are set as crashed can be configured with the "job_history.crashed_delay" configuration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->jobRepository->crashLongRunning($this->crashedDelay);

        return Command::SUCCESS;
    }
}
