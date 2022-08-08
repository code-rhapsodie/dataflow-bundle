<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Event;

use CodeRhapsodie\DataflowBundle\Entity\Job;

/**
 * Event used during the dataflow lifecycle.
 *
 * @codeCoverageIgnore
 */
class ProcessingEvent extends CrEvent
{
    /**
     * ProcessingEvent constructor.
     */
    public function __construct(private Job $job)
    {
    }

    public function getJob(): Job
    {
        return $this->job;
    }
}
