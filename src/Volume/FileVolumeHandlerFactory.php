<?php
namespace Oka\AttachmentManagerBundle\Volume;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class FileVolumeHandlerFactory implements VolumeHandlerFactoryInterface
{
    public function supports(string $dsn): bool
    {
        return str_starts_with($dsn, 'file://');
    }

    public function create(string $dsn, array $options = []): VolumeHandlerInterface
    {
        return new FileVolumeHandler();
    }
}
