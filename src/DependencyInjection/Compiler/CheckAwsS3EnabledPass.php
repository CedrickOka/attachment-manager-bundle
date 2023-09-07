<?php

namespace Oka\AttachmentManagerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class CheckAwsS3EnabledPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /** @var \Oka\AttachmentManagerBundle\Service\VolumeHandlerManager $volumeHandlerManager */
        $volumeHandlerManager = $container->get('oka_attachment_manager.volume_handler_manager');

        foreach ($volumeHandlerManager->getVolumes() as $volume) {
            if (str_starts_with($volume['dsn'], 's3://') && false === class_exists('Aws\S3\S3Client')) {
                throw new \LogicException('To use the S3 volume handler you have to install the "aws/aws-sdk-php".');
            }
        }
    }
}
