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
    /** @var Job */
    private $job;

    /**
     * ProcessingEvent constructor.
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public function getJob(): Job
    {
        return $this->job;
    }
}
