<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @codeCoverageIgnore
 */
class Frequency extends Constraint
{
    public $invalidMessage = 'The provided frequency "{{ string }}" must be a valid parameter for DateInterval::createFromDateString()';

    public $negativeIntervalMessage = 'The provided frequency "{{ string }}" mustn\'t represent a negative interval';
}
