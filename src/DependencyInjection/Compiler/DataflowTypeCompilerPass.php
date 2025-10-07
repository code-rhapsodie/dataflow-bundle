<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DependencyInjection\Compiler;

use CodeRhapsodie\DataflowBundle\Registry\DataflowTypeRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers dataflow types in the registry.
 *
 * @codeCoverageIgnore
 */
class DataflowTypeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(DataflowTypeRegistry::class)) {
            return;
        }

        $registry = $container->findDefinition(DataflowTypeRegistry::class);
        foreach ($container->findTaggedServiceIds('coderhapsodie.dataflow.type') as $id => $tags) {
            $registry->addMethodCall('registerDataflowType', [new Reference($id)]);
        }
    }
}
