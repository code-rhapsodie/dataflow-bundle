<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\MessengerMode;

class JobMessage
{
    /** @var int */
    private $jobId;

    public function __construct(int $jobId)
    {
        $this->jobId = $jobId;
    }

    public function getJobId(): int
    {
        return $this->jobId;
    }
}
