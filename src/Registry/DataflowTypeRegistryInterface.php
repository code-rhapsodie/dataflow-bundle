<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Registry;

use CodeRhapsodie\DataflowBundle\DataflowType\DataflowTypeInterface;

/**
 * Interfaces for dataflow types registries.
 */
interface DataflowTypeRegistryInterface
{
    /**
     * Get a registered dataflow type from its FQCN or one of its aliases.
     *
     * @param string $fqcnOrAlias
     *
     * @return DataflowTypeInterface
     */
    public function getDataflowType(string $fqcnOrAlias): DataflowTypeInterface;

    /**
     * Get all registered dataflow types.
     *
     * @return iterable|DataflowTypeInterface[]
     */
    public function listDataflowTypes(): iterable;

    /**
     * Registers a dataflow type.
     *
     * @param DataflowTypeInterface $dataflowType
     */
    public function registerDataflowType(DataflowTypeInterface $dataflowType): void;
}
