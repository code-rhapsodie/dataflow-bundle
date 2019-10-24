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
    public $message = 'The provided frequency "{{ string }}" must be a valid parameter for DateInterval::createFromDateString() and must not represent a negative value';
}
