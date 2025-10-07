<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DependencyInjection\Compiler;

use CodeRhapsodie\DataflowBundle\Processor\JobProcessor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class ExceptionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('coderhapsodie.dataflow.flysystem_service')) {
            return;
        }

        $flysystem = $container->getParameter('coderhapsodie.dataflow.flysystem_service');
        if (!$container->has($flysystem)) {
            throw new InvalidArgumentException(\sprintf('Service "%s" not found', $flysystem));
        }

        $definition = $container->findDefinition(JobProcessor::class);
        $definition->setArgument('$filesystem', new Reference($flysystem));
    }
}
