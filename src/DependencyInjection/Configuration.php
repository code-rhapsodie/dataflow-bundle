<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('code_rhapsodie_dataflow');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC for symfony/config < 4.2
            $rootNode = $treeBuilder->root('code_rhapsodie_dataflow');
        }

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
                        ->ifTrue(static function ($v): bool { return $v['enabled'] && !interface_exists(MessageBusInterface::class); })
                        ->thenInvalid('You need "symfony/messenger" in order to use Dataflow messenger mode.')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
