<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Writer;

/**
 * Represents a writer for dataflows.
 */
interface WriterInterface
{
    /**
     * Called before the dataflow is processed.
     */
    public function prepare();

    /**
     * Write an item.
     *
     * @param mixed $item
     */
    public function write($item);

    /**
     * Called after the dataflow is processed.
     */
    public function finish();
}
