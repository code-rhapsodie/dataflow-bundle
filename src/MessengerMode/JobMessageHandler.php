<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\MessengerMode;

use CodeRhapsodie\DataflowBundle\Processor\JobProcessorInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class JobMessageHandler implements MessageSubscriberInterface
{
    /** @var JobRepository */
    private $repository;

    /** @var JobProcessorInterface */
    private $processor;

    public function __construct(JobRepository $repository, JobProcessorInterface $processor)
    {
        $this->repository = $repository;
        $this->processor = $processor;
    }

    public function __invoke(JobMessage $message)
    {
        $this->processor->process($this->repository->find($message->getJobId()));
    }

    public static function getHandledMessages(): iterable
    {
        return [JobMessage::class];
    }
}
