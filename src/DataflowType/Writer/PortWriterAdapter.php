<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Writer;

class PortWriterAdapter implements WriterInterface
{
    /** @var \Port\Writer */
    private $writer;

    public function __construct(\Port\Writer $writer)
    {
        $this->writer = $writer;
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
