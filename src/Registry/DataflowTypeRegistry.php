<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Registry;

use CodeRhapsodie\DataflowBundle\Exceptions\UnknownDataflowTypeException;
use CodeRhapsodie\DataflowBundle\DataflowType\DataflowTypeInterface;

/**
 * Array based dataflow types registry.
 */
class DataflowTypeRegistry implements DataflowTypeRegistryInterface
{
    /** @var array|DataflowTypeInterface[] */
    private $fqcnRegistry = [];

    /** @var array|DataflowTypeInterface[] */
    private $aliasesRegistry = [];

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

        throw new UnknownDataflowTypeException($fqcnOrAlias);
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
        $this->fqcnRegistry[get_class($dataflowType)] = $dataflowType;
        foreach ($dataflowType->getAliases() as $alias) {
            $this->aliasesRegistry[$alias] = $dataflowType;
        }
    }
}
