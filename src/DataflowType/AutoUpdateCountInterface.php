<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType;

use CodeRhapsodie\DataflowBundle\Repository\JobRepository;

interface AutoUpdateCountInterface
{
    public function setRepository(JobRepository $repository): void;
}
