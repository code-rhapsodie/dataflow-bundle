<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Writer;

class PortWriterAdapter implements WriterInterface
{
    public function __construct(private readonly \Port\Writer $writer)
    {
    }

    public function prepare(): void
    {
        $this->writer->prepare();
    }

    public function write($item): void
    {
        $this->writer->writeItem((array) $item);
    }

    public function finish(): void
    {
        $this->writer->finish();
    }
}
