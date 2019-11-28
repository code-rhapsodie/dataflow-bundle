<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Dataflow;

use CodeRhapsodie\DataflowBundle\DataflowType\Result;

/**
 * Combines a reader, steps and writers as a data processing workflow.
 */
interface DataflowInterface
{
    /**
     * Processes the data.
     */
    public function process(): Result;
}
