<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Runner;

use CodeRhapsodie\DataflowBundle\DataflowType\Result;
use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Event\Events;
use CodeRhapsodie\DataflowBundle\Event\ProcessingEvent;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PendingDataflowRunner implements PendingDataflowRunnerInterface
{
    /** @var JobRepository */
    private $repository;

    /** @var DataflowTypeRegistryInterface */
    private $registry;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(JobRepository $repository, DataflowTypeRegistryInterface $registry, EventDispatcherInterface $dispatcher)
    {
        $this->repository = $repository;
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function runPendingDataflows(): void
    {
        while (null !== ($job = $this->repository->findNextPendingDataflow())) {
            $this->beforeProcessing($job);

            $dataflowType = $this->registry->getDataflowType($job->getDataflowType());
            $result = $dataflowType->process($job->getOptions());

            $this->afterProcessing($job, $result);
        }
    }

    /**
     * @param Job $job
     */
    private function beforeProcessing(Job $job): void
    {
        // Symfony 3.4 to 4.4 call
        if (!class_exists('Symfony\Contracts\EventDispatcher\Event')) {
            $this->dispatcher->dispatch(Events::BEFORE_PROCESSING, new ProcessingEvent($job));
        } else { // Symfony 5.0+ call
            $this->dispatcher->dispatch(new ProcessingEvent($job), Events::BEFORE_PROCESSING);
        }

        $job
            ->setStatus(Job::STATUS_RUNNING)
            ->setStartTime(new \DateTime())
        ;
        $this->repository->save($job);
    }

    /**
     * @param Job    $job
     * @param Result $result
     */
    private function afterProcessing(Job $job, Result $result): void
    {
        $exceptions = [];
        /** @var \Exception $exception */
        foreach ($result->getExceptions() as $exception) {
            $exceptions[] = (string) $exception;
        }

        $job
            ->setEndTime($result->getEndTime())
            ->setStatus(Job::STATUS_COMPLETED)
            ->setCount($result->getSuccessCount())
            ->setExceptions($exceptions)
        ;
        $this->repository->save($job);

        // Symfony 3.4 to 4.4 call
        if (!class_exists('Symfony\Contracts\EventDispatcher\Event')) {
            $this->dispatcher->dispatch(Events::AFTER_PROCESSING, new ProcessingEvent($job));
        } else { // Symfony 5.0+ call
            $this->dispatcher->dispatch(new ProcessingEvent($job), Events::AFTER_PROCESSING);
        }
    }
}
