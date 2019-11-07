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
                ->scalarNode('dbal_default_connection')
                    ->defaultValue('default')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
