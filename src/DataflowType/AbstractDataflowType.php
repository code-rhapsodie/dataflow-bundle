<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractDataflowType implements DataflowTypeInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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
        if ($dataflow instanceof LoggerAwareInterface && $this->logger instanceof LoggerInterface) {
            $dataflow->setLogger($this->logger);
        }

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
