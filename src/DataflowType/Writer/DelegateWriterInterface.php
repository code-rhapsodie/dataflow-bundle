<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Writer;

/**
 * A writer that can be used as a delegate of DelegatorWriter.
 */
interface DelegateWriterInterface extends WriterInterface
{
    /**
     * Returns true if the argument is of a supported type.
     *
     * @param $item
     *
     * @return bool
     */
    public function supports($item): bool;
}
