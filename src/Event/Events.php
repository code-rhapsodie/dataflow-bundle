<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Event;

final class Events
{
    public const BEFORE_PROCESSING = 'coderhapsodie.dataflow.before_processing';
    public const AFTER_PROCESSING = 'coderhapsodie.dataflow.after_processing';
}
