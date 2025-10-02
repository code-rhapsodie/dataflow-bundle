<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType;

use CodeRhapsodie\DataflowBundle\Repository\JobRepository;

interface RepositoryInterface
{
    public function setRepository(JobRepository $repository): void;
}