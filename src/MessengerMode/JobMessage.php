<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\MessengerMode;

class JobMessage
{
    public function __construct(private int $jobId)
    {
    }

    public function getJobId(): int
    {
        return $this->jobId;
    }
}
