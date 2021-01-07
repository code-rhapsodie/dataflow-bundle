<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

final class DelegatingLogger extends AbstractLogger
{
    /** @var LoggerInterface[] */
    private $loggers;

    public function __construct(iterable $loggers)
    {
        foreach ($loggers as $logger) {
            if (!$logger instanceof LoggerInterface) {
                throw new \InvalidArgumentException(sprintf('Only instances of %s should be passed to the constructor of %s. An instance of %s was passed instead.', LoggerInterface::class, self::class, get_class($logger)));
            }

            $this->loggers[] = $logger;
        }
    }

    public function log($level, $message, array $context = [])
    {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }
}
