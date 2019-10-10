<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Runner;

interface PendingDataflowRunnerInterface
{
    public function runPendingDataflows(): void;
}
