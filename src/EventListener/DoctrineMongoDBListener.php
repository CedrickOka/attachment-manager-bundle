<?php

namespace Oka\AttachmentManagerBundle\EventListener;

use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Oka\AttachmentManagerBundle\Model\AbstractDoctrineListener;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class DoctrineMongoDBListener extends AbstractDoctrineListener
{
    public function getSubscribedEvents(): array
    {
        return [
            ...parent::getSubscribedEvents(),
            Events::loadClassMetadata,
        ];
    }

    protected function doLoadClassMetadata(ClassMetadata $classMetadata): void
    {
        $classMetadata->mapManyReference([
            'fieldName' => 'attachments',
            'targetDocument' => $this->className,
            'isCascadeRemove' => true,
            'orphanRemoval' => true,
            'storeAs' => ClassMetadata::REFERENCE_STORE_AS_DB_REF_WITH_DB,
            'storeEmptyArray' => false,
        ]);
    }
}
