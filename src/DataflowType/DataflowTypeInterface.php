<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType;

use CodeRhapsodie\DataflowBundle\Repository\JobRepository;

interface DataflowTypeInterface
{
    public function getLabel(): string;

    public function getAliases(): iterable;

    public function process(array $options, ?int $jobId = null): Result;
}
