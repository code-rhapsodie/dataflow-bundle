<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Writer;

interface WriterInterface
{
    public function prepare();

    public function write($item);

    public function finish();
}
