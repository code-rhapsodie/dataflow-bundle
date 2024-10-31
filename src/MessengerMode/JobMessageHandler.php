<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\MessengerMode;

use CodeRhapsodie\DataflowBundle\Processor\JobProcessorInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class JobMessageHandler
{
    public function __construct(private JobRepository $repository, private JobProcessorInterface $processor)
    {
    }

    public function __invoke(JobMessage $message)
    {
        $this->processor->process($this->repository->find($message->getJobId()));
    }
}
