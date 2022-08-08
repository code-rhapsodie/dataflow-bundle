<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Writer;

class PortWriterAdapter implements WriterInterface
{
    public function __construct(private \Port\Writer $writer)
    {
    }

    public function prepare()
    {
        $this->writer->prepare();
    }

    public function write($item)
    {
        $this->writer->writeItem((array) $item);
    }

    public function finish()
    {
        $this->writer->finish();
    }
}
