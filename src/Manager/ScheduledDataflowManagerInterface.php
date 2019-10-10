<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Manager;

/**
 * Handles scheduled dataflows execution dates based on their frequency.
 */
interface ScheduledDataflowManagerInterface
{
    /**
     * Creates Job for each scheduled dataflow where next run date has been reached.
     */
    public function createJobsFromScheduledDataflows(): void;
}
