<?php
namespace Oka\AttachmentManagerBundle\Service;

use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Oka\AttachmentManagerBundle\Volume\Volume;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 * @method bool         exists(string $volumeName)
 * @method Volume       create(string $volumeName)
 * @method void         delete(string $volumeName, bool $recursive = false)
 * @method void         putFile(string $volumeName, AttachmentInterface $attachment, \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile)
 * @method \SplFileInfo getFileInfo(string $volumeName, AttachmentInterface $attachment)
 * @method void         deleteFile(string $volumeName, AttachmentInterface $attachment)
 * @method string       getFilePublicUrl(string $volumeName, AttachmentInterface $attachment)
 */
class VolumeHandlerManager
{
    private $volumeHandlerFactories;
    
    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag
     */
    private $volumes;
    
    public function __construct(iterable $volumeHandlerFactories, array $volumes)
    {
        $this->volumeHandlerFactories = $volumeHandlerFactories;
        $this->volumes = new ParameterBag($volumes);
    }
    
    public function __call($method, $args)
    {
        if (false === in_array($method, ['exists', 'create', 'delete', 'putFile', 'getFileInfo', 'deleteFile', 'getFilePublicUrl'], SORT_REGULAR)) {
            throw new \BadMethodCallException(sprintf('The method "%s" is not available.', $method));
        }
        
        if (!is_string($args[0])) {
            throw new \InvalidArgumentException(sprintf('The magic method require first argument must be type "string".', Volume::class));
        }
        
        if (false === in_array($method, ['exists', 'create', 'delete']) && !$args[1] instanceof AttachmentInterface) {
            throw new \InvalidArgumentException(sprintf('The magic method require second argument must be type "%s" class.', AttachmentInterface::class));
        }
        
        if (!$this->volumes->has($args[0])) {
            throw new \InvalidArgumentException(sprintf('The volume with the name "%s" does not exist.', $args[0]));
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
        
        return $volumeHandler->{$method}(...$args);
    }
}
