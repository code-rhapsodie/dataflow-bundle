<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('code_rhapsodie_dataflow');

        $rootNode
            ->children()
                ->arrayNode('dbal_connections')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($v) {
                            return [$v];
                        })
                    ->end()
                    ->defaultValue(['default'])
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
