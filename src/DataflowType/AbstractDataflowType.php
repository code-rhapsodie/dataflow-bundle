<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType;

use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractDataflowType implements DataflowTypeInterface, LoggerAwareInterface, RepositoryInterface
{
    use LoggerAwareTrait;

    private JobRepository $repository;

    private ?\DateTime $saveDate = null;

    /**
     * @codeCoverageIgnore
     */
    public function getAliases(): iterable
    {
        return [];
    }

    public function process(array $options, ?int $jobId = null): Result
    {
        $this->saveDate = new \DateTime();

        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $options = $optionsResolver->resolve($options);

        $builder = $this->createDataflowBuilder();
        $builder->setName($this->getLabel());
        $builder->addAfterItemProcessors(function (int|string $index, mixed $item, int $count) use ($jobId) {
            if ($jobId === null || $this->saveDate > new \DateTime()) {
                return;
            }

            $this->repository->updateCount($jobId, $count);
            $this->saveDate = new \DateTime('+1 minute');
        });
        $this->buildDataflow($builder, $options);
        $dataflow = $builder->getDataflow();
        if ($dataflow instanceof LoggerAwareInterface && $this->logger instanceof LoggerInterface) {
            $dataflow->setLogger($this->logger);
        }

        return $dataflow->process();
    }

    protected function createDataflowBuilder(): DataflowBuilder
    {
        return new DataflowBuilder();
    }

    /**
     * @codeCoverageIgnore
     */
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
    }

    abstract protected function buildDataflow(DataflowBuilder $builder, array $options): void;

    public function setRepository(JobRepository $repository): void
    {
        $this->repository = $repository;
    }
}
