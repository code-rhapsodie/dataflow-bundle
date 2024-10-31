<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): \Symfony\Component\Config\Definition\Builder\TreeBuilder
    {
        $treeBuilder = new TreeBuilder('code_rhapsodie_dataflow');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('dbal_default_connection')
                    ->defaultValue('default')
                ->end()
                ->scalarNode('default_logger')
                    ->defaultValue('logger')
                ->end()
                ->arrayNode('messenger_mode')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('bus')
                            ->defaultValue('messenger.default_bus')
                        ->end()
                    ->end()
                    ->validate()
                        ->ifTrue(static fn($v): bool => $v['enabled'] && !interface_exists(MessageBusInterface::class))
                        ->thenInvalid('You need "symfony/messenger" in order to use Dataflow messenger mode.')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
