<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FrequencyValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof Frequency) {
            throw new UnexpectedTypeException($constraint, Frequency::class);
        }

        if (null === $value) {
            return;
        }

        $interval = @\DateInterval::createFromDateString($value);
        if (!$interval) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation()
            ;

            return;
        }

        $now = new \DateTime();
        $dt = clone $now;
        $dt->add($interval);

        if ($dt <= $now) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation()
            ;
        }
    }
}
