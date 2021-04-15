<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DependencyInjection\Compiler;

use CodeRhapsodie\DataflowBundle\Runner\MessengerDataflowRunner;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class BusCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('coderhapsodie.dataflow.bus')) {
            return;
        }

        $bus = $container->getParameter('coderhapsodie.dataflow.bus');
        if (!$container->has($bus)) {
            throw new InvalidArgumentException(sprintf('Service "%s" not found', $bus));
        }

        if (!$container->has(MessengerDataflowRunner::class)) {
            return;
        }

        $definition = $container->findDefinition(MessengerDataflowRunner::class);
        $definition->setArgument('$bus', new Reference($bus));
    }
}
