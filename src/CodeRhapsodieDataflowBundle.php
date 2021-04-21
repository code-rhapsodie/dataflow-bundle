<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle;

use CodeRhapsodie\DataflowBundle\DependencyInjection\CodeRhapsodieDataflowExtension;
use CodeRhapsodie\DataflowBundle\DependencyInjection\Compiler\BusCompilerPass;
use CodeRhapsodie\DataflowBundle\DependencyInjection\Compiler\DataflowTypeCompilerPass;
use CodeRhapsodie\DataflowBundle\DependencyInjection\Compiler\DefaultLoggerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @codeCoverageIgnore
 */
class CodeRhapsodieDataflowBundle extends Bundle
{
    protected $name = 'CodeRhapsodieDataflowBundle';

    public function getContainerExtension()
    {
        return new CodeRhapsodieDataflowExtension();
    }

    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new DataflowTypeCompilerPass())
            ->addCompilerPass(new DefaultLoggerCompilerPass())
            ->addCompilerPass(new BusCompilerPass())
        ;
    }
}
