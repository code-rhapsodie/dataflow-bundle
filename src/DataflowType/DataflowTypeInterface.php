<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType;

interface DataflowTypeInterface
{
    public function getLabel(): string;

    public function getAliases(): iterable;

    public function process(array $options): Result;
}
