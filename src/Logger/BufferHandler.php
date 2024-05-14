<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Logger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;

class BufferHandler extends AbstractProcessingHandler
{
    private const FORMAT = "[%datetime%] %level_name% when processing item %context.index%: %message% %context% %extra%\n";

    private array $buffer = [];

    public function clearBuffer(): array
    {
        $logs = $this->buffer;
        $this->buffer = [];

        return $logs;
    }

    protected function write(array|LogRecord $record): void
    {
        $this->buffer[] = $record['formatted'];
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter(self::FORMAT);
    }
}
