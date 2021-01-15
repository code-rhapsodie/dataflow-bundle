<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Exceptions;

/**
 * Exception thrown when an unknown dataflow type is requested from the registry.
 */
class UnknownDataflowTypeException extends \Exception
{
    public static function create(string $aliasOrFqcn, array $knownDataflowTypes): self
    {
        return new self(sprintf(
            'Unknown dataflow type FQCN or alias "%s". Registered dataflow types FQCN and aliases are %s.',
            $aliasOrFqcn,
            implode(', ', $knownDataflowTypes)
        ));
    }
}
