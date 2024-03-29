<?php

namespace Oka\AttachmentManagerBundle\EventListener;

use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Oka\AttachmentManagerBundle\Model\AbstractDoctrineListener;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class DoctrineMongoDBListener extends AbstractDoctrineListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        /** @var ClassMetadata $classMetadata */
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
            'orphanRemoval' => true,
            'storeAs' => ClassMetadata::REFERENCE_STORE_AS_DB_REF_WITH_DB,
            'storeEmptyArray' => false,
        ]);
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }
}
