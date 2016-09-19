<?php

namespace PM\ChainCommandBundle;

use PM\ChainCommandBundle\DependencyInjection\Compiler\ChainCompilerPath;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PMChainCommandBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ChainCompilerPath());
    }
}
