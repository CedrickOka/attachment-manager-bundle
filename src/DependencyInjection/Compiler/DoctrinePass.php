<?php

namespace Oka\AttachmentManagerBundle\DependencyInjection\Compiler;

use Doctrine\Persistence\ObjectManager;
use Oka\AttachmentManagerBundle\Model\AttachmentManagerInterface;
use Oka\AttachmentManagerBundle\OkaAttachmentManagerBundle;
use Oka\AttachmentManagerBundle\Service\AttachmentManager;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class DoctrinePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $attachmentManagerServiceIds = [];

        foreach (OkaAttachmentManagerBundle::$doctrineDrivers as $key => $dbDriver) {
            if (!$container->hasParameter(sprintf('oka_attachment_manager.backend_type.%s', $key))) {
                continue;
            }

            if (false === $container->hasDefinition($dbDriver['registry'])) {
                throw new \InvalidArgumentException(sprintf('To use database driver "%s" you have to install the "%s".', $key, $dbDriver['bundle']));
            }

            $registryAliasId = sprintf('oka_attachment_manager.%s.doctrine_registry', $key);
            $objectManagerServiceId = sprintf('oka_attachment_manager.%s.object_manager', $key);
            $attachmentManagerServiceId = sprintf('oka_attachment_manager.%s.attachment_manager', $key);

            $container->setAlias($registryAliasId, new Alias($dbDriver['registry'], false));

            $container
                ->setDefinition($objectManagerServiceId, new Definition(ObjectManager::class, [new Parameter(sprintf('oka_attachment_manager.%s.model_manager_name', $key))]))
                ->setFactory([new Reference($registryAliasId), 'getManager']);

            $container->setDefinition(
                $attachmentManagerServiceId,
                new Definition(
                    AttachmentManager::class,
                    [
                        new Parameter('oka_attachment_manager.prefix_separator'),
                        new Parameter(sprintf('oka_attachment_manager.%s.class', $key)),
                        $container->getParameter(sprintf('oka_attachment_manager.%s.related_objects', $key)),
                        new Reference($objectManagerServiceId),
                        new Reference('oka_attachment_manager.volume_handler_manager'),
                        new Reference('event_dispatcher'),
                    ]
                )
            );

            $attachmentManagerServiceIds[$key] = new Reference($attachmentManagerServiceId);
        }

        $container
            ->getDefinition('oka_attachment_manager.attachment_manager_locator')
            ->addArgument($attachmentManagerServiceIds);

        if (1 === count($attachmentManagerServiceIds)) {
            $container->setAlias(AttachmentManagerInterface::class, array_values($attachmentManagerServiceIds)[0]->__toString());
        }
    }
}
