<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Event;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event used during the dataflow lifecycle.
 *
 * @codeCoverageIgnore
 */
class ProcessingEvent extends Event
{
    /** @var Job */
    private $job;

    /**
     * ProcessingEvent constructor.
     *
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    /**
     * @return Job
     */
    public function getJob(): Job
    {
        return $this->job;
    }
}
