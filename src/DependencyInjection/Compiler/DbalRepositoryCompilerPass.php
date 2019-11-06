<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DependencyInjection\Compiler;

use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers repository for each dbal connection in the container.
 *
 * @codeCoverageIgnore
 */
class DbalRepositoryCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('coderhapsodie.dataflow.dbal_connections')) {
            return;
        }

        $dbalConnections = $container->getParameter('coderhapsodie.dataflow.dbal_connections');

        foreach ($dbalConnections as $connection) {
            $connectionName = sprintf('doctrine.dbal.%s_connection', $connection);
            if (!$container->has($connectionName)) {
                throw new \Exception('Unable to find the connection '.$connectionName);
            }
            $def = new Definition(JobRepository::class, [new Reference($connectionName)]);
            $def->setPublic(false);
            $def->addTag('coderhapsodie.dataflow.job_repository');
            $container->register('coderhapsodie.dataflow.job_repository.'.$connection, $def);

            $def = new Definition(ScheduledDataflowRepository::class, [new Reference($connectionName)]);
            $def->setPublic(false);
            $def->addTag('coderhapsodie.dataflow.schedule_repository');
            $container->register('coderhapsodie.dataflow.schedule_repository.'.$connection, $def);
        }
    }
}
