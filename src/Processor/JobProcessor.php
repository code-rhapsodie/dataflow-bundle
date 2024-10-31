<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Processor;

use CodeRhapsodie\DataflowBundle\DataflowType\Result;
use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Event\Events;
use CodeRhapsodie\DataflowBundle\Event\ProcessingEvent;
use CodeRhapsodie\DataflowBundle\Logger\BufferHandler;
use CodeRhapsodie\DataflowBundle\Logger\DelegatingLogger;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JobProcessor implements JobProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private JobRepository $repository, private DataflowTypeRegistryInterface $registry, private EventDispatcherInterface $dispatcher)
    {
    }

    public function process(Job $job): void
    {
        $this->beforeProcessing($job);

        $dataflowType = $this->registry->getDataflowType($job->getDataflowType());
        $loggers = [new Logger('dataflow_internal', [$bufferHandler = new BufferHandler()])];
        if (isset($this->logger)) {
            $loggers[] = $this->logger;
        }
        $logger = new DelegatingLogger($loggers);

        if ($dataflowType instanceof LoggerAwareInterface) {
            $dataflowType->setLogger($logger);
        }

        $result = $dataflowType->process($job->getOptions());

        if (!$dataflowType instanceof LoggerAwareInterface) {
            foreach ($result->getExceptions() as $index => $e) {
                $logger->error($e, ['index' => $index]);
            }
        }

        $this->afterProcessing($job, $result, $bufferHandler);
    }

    private function beforeProcessing(Job $job): void
    {
        $this->dispatcher->dispatch(new ProcessingEvent($job), Events::BEFORE_PROCESSING);

        $job
            ->setStatus(Job::STATUS_RUNNING)
            ->setStartTime(new \DateTime())
        ;
        $this->repository->save($job);
    }

    private function afterProcessing(Job $job, Result $result, BufferHandler $bufferLogger): void
    {
        $job
            ->setEndTime($result->getEndTime())
            ->setStatus(Job::STATUS_COMPLETED)
            ->setCount($result->getSuccessCount())
            ->setExceptions($bufferLogger->clearBuffer())
        ;
        $this->repository->save($job);

        $this->dispatcher->dispatch(new ProcessingEvent($job), Events::AFTER_PROCESSING);
    }
}
