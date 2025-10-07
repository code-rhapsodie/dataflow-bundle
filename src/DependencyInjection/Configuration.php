<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
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
                        ->ifTrue(static fn ($v): bool => $v['enabled'] && !interface_exists(MessageBusInterface::class))
                        ->thenInvalid('You need "symfony/messenger" in order to use Dataflow messenger mode.')
                    ->end()
                ->end()
                ->arrayNode('exceptions_mode')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('type')
                            ->defaultValue('database')
                        ->end()
                        ->scalarNode('flysystem_service')
                        ->end()
                        ->validate()
                            ->ifTrue(static fn($v): bool => 'file' === $v['type'] && !interface_exists('\League\Flysystem\Filesystem'))
                            ->thenInvalid('You need "league/flysystem" to use Dataflow file exception mode.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
