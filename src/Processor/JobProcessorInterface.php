<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Processor;

use CodeRhapsodie\DataflowBundle\Entity\Job;

interface JobProcessorInterface
{
    public function process(Job $job): void;
}
