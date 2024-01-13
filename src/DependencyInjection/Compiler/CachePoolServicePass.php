<?php

namespace Oka\AttachmentManagerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class CachePoolServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (null === ($cacheId = $container->getParameter('oka_attachment_manager.cache_id'))) {
            return;
        }

        if (false === $container->hasDefinition($cacheId)) {
            return;
        }

        $definition = $container->getDefinition('oka_attachment_manager.volume_handler_manager');
        $definition->replaceArgument(2, new Reference($cacheId));
    }
}
