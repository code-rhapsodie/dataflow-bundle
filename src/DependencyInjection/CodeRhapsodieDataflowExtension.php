<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DependencyInjection;

use CodeRhapsodie\DataflowBundle\DataflowType\DataflowTypeInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @codeCoverageIgnore
 */
class CodeRhapsodieDataflowExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container
            ->registerForAutoconfiguration(DataflowTypeInterface::class)
            ->addTag('coderhapsodie.dataflow.type')
        ;
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('coderhapsodie.dataflow.dbal_default_connection', $config['dbal_default_connection']);
    }
}
