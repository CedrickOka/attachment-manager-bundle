<?php

namespace Oka\AttachmentManagerBundle\Service;

use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Oka\AttachmentManagerBundle\Volume\Volume;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 * @method bool                                         exists(string $volumeName)
 * @method \Oka\AttachmentManagerBundle\Volume\Volume   create(string $volumeName)
 * @method void                                         delete(string $volumeName, bool $recursive = false)
 * @method void                                         putFile(AttachmentInterface $attachment, \Symfony\Component\HttpFoundation\File\File $file)
 * @method \Oka\AttachmentManagerBundle\Volume\FileInfo getFileInfo(AttachmentInterface $attachment)
 * @method void                                         deleteFile(AttachmentInterface $attachment)
 * @method string                                       getFilePublicUrl(AttachmentInterface $attachment)
 */
class VolumeHandlerManager
{
    private $volumeHandlerFactories;

    /**
     * @var ParameterBag
     */
    private $volumes;

    private $cachePool;

    public function __construct(iterable $volumeHandlerFactories, array $volumes, CacheItemPoolInterface $cachePool = null)
    {
        $this->volumeHandlerFactories = $volumeHandlerFactories;
        $this->volumes = new ParameterBag($volumes);
        $this->cachePool = $cachePool;
    }

    public function getVolumes(): ParameterBag
    {
        return $this->volumes;
    }

    public function __call($method, $args)
    {
        if (false === in_array($method, ['exists', 'create', 'delete', 'putFile', 'getFileInfo', 'deleteFile', 'getFilePublicUrl'], SORT_REGULAR)) {
            throw new \BadMethodCallException(sprintf('The method "%s" is not available.', $method));
        }
        
        if (true === in_array($method, ['exists', 'create', 'delete'])) {
            if (!is_string($args[0])) {
                throw new \InvalidArgumentException(sprintf('The magic method require first argument must be type "string".', Volume::class));
            }
        } else {
            if (!$args[0] instanceof AttachmentInterface) {
                throw new \InvalidArgumentException(sprintf('The magic method require first argument must be type "%s" class.', AttachmentInterface::class));
            }

            array_unshift($args, $args[0]->getVolumeName());
            $args = array_values($args);
        }

        $configuration = $this->volumes->get($args[0]);
        $args[0] = new Volume($args[0], $configuration['dsn'], $configuration['options'], $configuration['public_url']);

        /** @var \Oka\AttachmentManagerBundle\Volume\VolumeHandlerFactoryInterface $factory */
        foreach ($this->volumeHandlerFactories as $factory) {
            if (false === $factory->supports($args[0]->getDsn())) {
                continue;
            }

            /** @var \Oka\AttachmentManagerBundle\Volume\VolumeHandlerFactoryInterface $factory */
            $volumeHandler = $factory->create($args[0]->getDsn(), $args[0]->getOptions());
            break;
        }

        if (false === isset($volumeHandler)) {
            throw new \LogicException(sprintf('No volume handler configured for volume dsn "%s".', $args[0]->getDsn()));
        }

        if ('getFileInfo' !== $method && 'getFilePublicUrl' !== $method) {
            return $volumeHandler->{$method}(...$args);
        }

        /** @var \Psr\Cache\CacheItemInterface $cacheItem */
        $cacheItem = $this->cachePool->getItem(sprintf('oka_attachment_manager.%s.%s.%s.%s', strtolower($args[1]->getVolumeName()), strtolower($method), md5($args[1]->getFilename()), $args[1]->getLastModified()->getTimestamp()));

        if (!$cacheItem->isHit()) {
            $cacheItem->set($volumeHandler->{$method}(...$args));
            $cacheItem->expiresAfter(86400);
            $this->cachePool->saveDeferred($cacheItem);
        }

        return $cacheItem->get();
    }
}
