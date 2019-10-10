<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Runner;

use CodeRhapsodie\DataflowBundle\DataflowType\Result;
use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Event\Events;
use CodeRhapsodie\DataflowBundle\Event\ProcessingEvent;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PendingDataflowRunner implements PendingDataflowRunnerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var JobRepository */
    private $repository;

    /** @var DataflowTypeRegistryInterface */
    private $registry;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EntityManagerInterface $em, JobRepository $repository, DataflowTypeRegistryInterface $registry, EventDispatcherInterface $dispatcher)
    {
        $this->em = $em;
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
        $this->dispatcher->dispatch(Events::BEFORE_PROCESSING, new ProcessingEvent($job));

        $job
            ->setStatus(Job::STATUS_RUNNING)
            ->setStartTime(new \DateTime())
        ;
        $this->em->flush();
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
        $this->em->flush();

        $this->dispatcher->dispatch(Events::AFTER_PROCESSING, new ProcessingEvent($job));
    }
}
