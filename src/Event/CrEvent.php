<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Event;

/*
 * @codeCoverageIgnore
 */
if (class_exists('Symfony\Contracts\EventDispatcher\Event')) {
    // For Symfony 5.0+
    abstract class CrEvent extends \Symfony\Contracts\EventDispatcher\Event
    {
    }
} else {
    // For Symfony 3.4 to 4.4
    abstract class CrEvent extends \Symfony\Component\EventDispatcher\Event
    {
    }
}
