<?php

namespace Oka\AttachmentManagerBundle\DependencyInjection;

use Oka\AttachmentManagerBundle\OkaAttachmentManagerBundle;
use Oka\AttachmentManagerBundle\Volume\VolumeHandlerFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class OkaAttachmentManagerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('oka_attachment_manager.prefix_separator', $config['prefix_separator']);

        $relatedObjectDBDriverMapping = [];
        $relatedObjectUploadMaxSizes = [];

        foreach (['orm', 'mongodb'] as $dbDriver) {
            if (false === $this->isConfigEnabled($container, $config[$dbDriver])) {
                continue;
            }

            $container->setParameter(sprintf('oka_attachment_manager.%s.model_manager_name', $dbDriver), $config[$dbDriver]['model_manager_name']);
            $container->setParameter(sprintf('oka_attachment_manager.%s.class', $dbDriver), $config[$dbDriver]['class']);
            $container->setParameter(sprintf('oka_attachment_manager.%s.related_objects', $dbDriver), $config[$dbDriver]['related_objects']);
            $container->setParameter(sprintf('oka_attachment_manager.backend_type.%s', $dbDriver), true);

            foreach ($config[$dbDriver]['related_objects'] as $name => $value) {
                $relatedObjectDBDriverMapping[$name] = $dbDriver;
                $relatedObjectUploadMaxSizes[$name] = $value['upload_max_size'];
            }

            $container
                ->setDefinition(
                    sprintf('oka_attachment_manager.%s.doctrine_listener', $dbDriver),
                    new Definition(OkaAttachmentManagerBundle::$doctrineDrivers[$dbDriver]['subscriber_class'], [$config[$dbDriver]['class']])
                )
                ->addTag(OkaAttachmentManagerBundle::$doctrineDrivers[$dbDriver]['tag']);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        $container
            ->getDefinition('oka_attachment_manager.is_related_object_name_validator')
            ->replaceArgument(0, array_keys($relatedObjectDBDriverMapping));

        $container
            ->getDefinition('oka_attachment_manager.uploaded_file_validator')
            ->replaceArgument(0, $relatedObjectUploadMaxSizes);

        $container
            ->getDefinition('oka_attachment_manager.attachment_controller')
            ->replaceArgument(3, $relatedObjectDBDriverMapping);

        $container
            ->getDefinition('oka_attachment_manager.volume_handler_manager')
            ->replaceArgument(1, $config['volumes']);

        $container
            ->registerForAutoconfiguration(VolumeHandlerFactoryInterface::class)
            ->addTag('oka_attachment_manager.volume_handler.factory');
    }
}
