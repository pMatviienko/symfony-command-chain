<?php
namespace PM\ChainCommandBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ChainCompilerPath implements CompilerPassInterface
{
    const CHAIN_TAG_NAME = 'pm.chain_command_to';
    const COMMAND_PARAMETER_NAME = 'command';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('pm.chaincommandbundle.chain.manager')) {
            return;
        }
        $definition = $container->findDefinition('pm.chaincommandbundle.chain.manager');

        $taggedServices = $container->findTaggedServiceIds(self::CHAIN_TAG_NAME);

        foreach ($taggedServices as $id => $tagsSet) {
            foreach ($tagsSet as $tags) {
                $definition->addMethodCall('addCommandToChain', [
                    $tags[self::COMMAND_PARAMETER_NAME],
                    new Reference($id),
                ]);
            }

        }
    }
}
