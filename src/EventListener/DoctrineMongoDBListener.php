<?php
namespace Oka\AttachmentManagerBundle\EventListener;

use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;
use Oka\AttachmentManagerBundle\Model\AbstractDoctrineListener;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class DoctrineMongoDBListener extends AbstractDoctrineListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        /** @var \Doctrine\ODM\MongoDB\Mapping\ClassMetadata $classMetadata */
        $classMetadata = $event->getClassMetadata();
        
        /** @var \ReflectionClass $reflClass */
        if (null === ($reflClass = $classMetadata->reflClass)) {
            return;
        }
        
        if (false === $this->isObjectSupported($reflClass)) {
            return;
        }
        
        $classMetadata->mapManyReference([
            'fieldName' => 'attachments',
            'targetDocument' => $this->className,
            'isCascadeRemove' => true,
        ]);
    }
    
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }
}
