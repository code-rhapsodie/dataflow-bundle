<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DependencyInjection\Compiler;

use CodeRhapsodie\DataflowBundle\Command\ExecuteDataflowCommand;
use CodeRhapsodie\DataflowBundle\Runner\PendingDataflowRunner;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DefaultLoggerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $defaultLogger = $container->getParameter('coderhapsodie.dataflow.default_logger');
        if (!$container->has($defaultLogger)) {
            return;
        }

        foreach ([ExecuteDataflowCommand::class, PendingDataflowRunner::class] as $serviceId) {
            if (!$container->has($serviceId)) {
                continue;
            }

            $definition = $container->findDefinition($serviceId);
            $definition->addMethodCall('setLogger', [new Reference($defaultLogger)]);
        }
    }
}
