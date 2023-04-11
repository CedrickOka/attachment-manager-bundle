<?php
namespace Oka\AttachmentManagerBundle\Volume;

use Aws\S3\S3Client;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class S3VolumeHandlerFactory implements VolumeHandlerFactoryInterface
{
    public function supports(string $dsn): bool
    {
        return str_starts_with($dsn, 's3://');
    }

    public function create(string $dsn, array $options = []): VolumeHandlerInterface
    {
        return new S3VolumeHandler(new S3Client([
            'version' => 'latest',
            'region' => 'africa',
            'use_path_style_endpoint' => true,
            ...$options,
        ]));
    }
}
