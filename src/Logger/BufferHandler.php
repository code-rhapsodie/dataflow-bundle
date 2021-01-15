<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Logger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class BufferHandler extends AbstractProcessingHandler
{
    private const FORMAT = "[%datetime%] %level_name% when processing item %context.index%: %message% %context% %extra%\n";

    private $buffer;

    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->buffer = [];
    }

    public function clearBuffer(): array
    {
        $logs = $this->buffer;
        $this->buffer = [];

        return $logs;
    }

    protected function write(array $record): void
    {
        $this->buffer[] = $record['formatted'];
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter(self::FORMAT);
    }
}
