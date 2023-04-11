<?php
namespace Oka\AttachmentManagerBundle\EventListener;

use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Oka\AttachmentManagerBundle\Model\AbstractDoctrineListener;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class DoctrineORMListener extends AbstractDoctrineListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata */
        $classMetadata = $event->getClassMetadata();
        
        /** @var \ReflectionClass $reflClass */
        if (null === ($reflClass = $classMetadata->reflClass)) {
            return;
        }
        
        if (false === $this->isObjectSupported($reflClass)) {
            return;
        }
        
        $classMetadata->mapManyToMany([
            'fieldName' => 'attachments',
            'targetEntity' => $this->className,
            'cascade' => ['all'],
            'fetch' => ClassMetadata::FETCH_EXTRA_LAZY,
            'joinTable' => [
                'name' => sprintf('%s_attachment', $classMetadata->getTableName()),
                'joinColumns' => [
                    [
                        'id' => true,
                        'name' => sprintf('%s_id', $classMetadata->getTableName()),
                        'referencedColumnName' => 'id',
                        'onDelete' => 'CASCADE',
                        'nullable' => false,
                    ],
                ],
                'inverseJoinColumns' => [
                    [
                        'id' => true,
                        'name' => 'attachment_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'CASCADE',
                        'nullable' => false,
                    ],
                    [
                        'id' => true,
                        'name' => 'attachment_related_object_class_name',
                        'referencedColumnName' => 'related_object_class_name',
                        'nullable' => false,
                    ],
                ]
            ],
        ]);
    }
    
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }
}
