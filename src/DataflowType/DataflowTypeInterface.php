<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType;

use Psr\Log\LoggerAwareInterface;

interface DataflowTypeInterface extends LoggerAwareInterface
{
    public function getLabel(): string;

    public function getAliases(): iterable;

    public function process(array $options, ?int $jobId = null): Result;
}
