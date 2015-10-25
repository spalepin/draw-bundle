<?php

namespace Draw\DrawBundle;

use Draw\DrawBundle\DependencyInjection\CompilerPass;
use Draw\DrawBundle\DependencyInjection\DrawDrawExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawDrawBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CompilerPass());
    }

    public function getContainerExtension()
    {
        return new DrawDrawExtension();
    }
}
