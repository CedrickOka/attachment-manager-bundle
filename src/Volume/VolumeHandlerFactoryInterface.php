<?php
namespace Oka\AttachmentManagerBundle\Volume;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
interface VolumeHandlerFactoryInterface
{
    public function supports(string $dsn): bool;
        
    public function create(string $dsn, array $options = []): VolumeHandlerInterface;
}
