<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractDataflowType implements DataflowTypeInterface
{
    /**
     * @codeCoverageIgnore
     */
    public function getAliases(): iterable
    {
        return [];
    }

    public function process(array $options): Result
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $options = $optionsResolver->resolve($options);

        $builder = (new DataflowBuilder())
            ->setName($this->getLabel())
        ;
        $this->buildDataflow($builder, $options);
        $dataflow = $builder->getDataflow();

        return $dataflow->process();
    }

    /**
     * @codeCoverageIgnore
     */
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
    }

    abstract protected function buildDataflow(DataflowBuilder $builder, array $options): void;
}
