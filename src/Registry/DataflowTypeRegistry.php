<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Registry;

use CodeRhapsodie\DataflowBundle\DataflowType\DataflowTypeInterface;
use CodeRhapsodie\DataflowBundle\Exceptions\UnknownDataflowTypeException;

/**
 * Array based dataflow types registry.
 */
class DataflowTypeRegistry implements DataflowTypeRegistryInterface
{
    /** @var array|DataflowTypeInterface[] */
    private array $fqcnRegistry = [];

    /** @var array|DataflowTypeInterface[] */
    private array $aliasesRegistry = [];

    /**
     * {@inheritdoc}
     */
    public function getDataflowType(string $fqcnOrAlias): DataflowTypeInterface
    {
        if (isset($this->fqcnRegistry[$fqcnOrAlias])) {
            return $this->fqcnRegistry[$fqcnOrAlias];
        }

        if (isset($this->aliasesRegistry[$fqcnOrAlias])) {
            return $this->aliasesRegistry[$fqcnOrAlias];
        }

        throw UnknownDataflowTypeException::create($fqcnOrAlias, [...array_keys($this->fqcnRegistry), ...array_keys($this->aliasesRegistry)]);
    }

    /**
     * {@inheritdoc}
     */
    public function listDataflowTypes(): iterable
    {
        return $this->fqcnRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function registerDataflowType(DataflowTypeInterface $dataflowType): void
    {
        $this->fqcnRegistry[$dataflowType::class] = $dataflowType;
        foreach ($dataflowType->getAliases() as $alias) {
            $this->aliasesRegistry[$alias] = $dataflowType;
        }
    }
}
