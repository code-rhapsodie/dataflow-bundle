<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Processor;

use CodeRhapsodie\DataflowBundle\DataflowType\AutoUpdateCountInterface;
use CodeRhapsodie\DataflowBundle\DataflowType\Result;
use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Event\Events;
use CodeRhapsodie\DataflowBundle\Event\ProcessingEvent;
use CodeRhapsodie\DataflowBundle\ExceptionsHandler\ExceptionHandlerInterface;
use CodeRhapsodie\DataflowBundle\ExceptionsHandler\NullExceptionHandler;
use CodeRhapsodie\DataflowBundle\Gateway\JobGateway;
use CodeRhapsodie\DataflowBundle\Logger\DelegatingLogger;
use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistryInterface;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JobProcessor implements JobProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    private const FORMAT = "[%datetime%] %level_name% when processing item %context.index%: %message% %context% %extra%\n";

    public function __construct(
        private JobRepository $repository,
        private DataflowTypeRegistryInterface $registry,
        private EventDispatcherInterface $dispatcher,
        private JobGateway $jobGateway,
        private ExceptionHandlerInterface $exceptionHandler,
    ) {
    }

    public function process(Job $job): void
    {
        $this->beforeProcessing($job);

        $dataflowType = $this->registry->getDataflowType($job->getDataflowType());
        if ($dataflowType instanceof AutoUpdateCountInterface) {
            $dataflowType->setRepository($this->repository);
        }

        $handler = new StreamHandler(tempnam(sys_get_temp_dir(), 'dataflow_'), fileOpenMode: 'w+');
        $handler->setFormatter(new LineFormatter(self::FORMAT));

        $loggers = [new Logger('dataflow_internal', [$bufferHandler = $handler])];
        if (isset($this->logger)) {
            $loggers[] = $this->logger;
        }
        $logger = new DelegatingLogger($loggers);

        $dataflowType->setLogger($logger);

        $result = $dataflowType->process($job->getOptions(), $job->getId());

        $this->afterProcessing($job, $result, $bufferHandler);
    }

    private function beforeProcessing(Job $job): void
    {
        $this->dispatcher->dispatch(new ProcessingEvent($job), Events::BEFORE_PROCESSING);

        $job
            ->setStatus(Job::STATUS_RUNNING)
            ->setStartTime(new \DateTime())
        ;
        $this->jobGateway->save($job);
    }

    private function afterProcessing(Job $job, Result $result, StreamHandler $streamHandler): void
    {
        $job
            ->setEndTime($result->getEndTime())
            ->setStatus(Job::STATUS_COMPLETED)
            ->setCount($result->getSuccessCount())
            ->setExceptionCount($result->getErrorCount())
        ;

        if (!$this->exceptionHandler instanceof NullExceptionHandler) {
            $this->exceptionHandler->save($job->getId(), $streamHandler->getStream());
            $job->setStreamExceptions($streamHandler->getStream());
        } else {
            $job->setExceptions(json_decode(stream_get_contents($streamHandler->getStream()), true));
            $streamHandler->reset();
        }

        $this->jobGateway->save($job);

        $this->dispatcher->dispatch(new ProcessingEvent($job), Events::AFTER_PROCESSING);
    }
}
