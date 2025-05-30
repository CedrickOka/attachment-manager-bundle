<?php

namespace Oka\AttachmentManagerBundle\EventListener;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Oka\AttachmentManagerBundle\Model\AbstractDoctrineListener;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class DoctrineMongoDBListener extends AbstractDoctrineListener
{
    protected function doLoadClassMetadata(ClassMetadata $classMetadata, \ReflectionClass $reflClass): void
    {
        if (!$classMetadata instanceof \Doctrine\ODM\MongoDB\Mapping\ClassMetadata) {
            return;
        }

        $classMetadata->mapManyReference([
            'fieldName' => 'attachments',
            'targetDocument' => $this->className,
            'isCascadeRemove' => true,
            'orphanRemoval' => true,
            'storeAs' => \Doctrine\ODM\MongoDB\Mapping\ClassMetadata::REFERENCE_STORE_AS_DB_REF_WITH_DB,
            'storeEmptyArray' => false,
        ]);
    }
}
