<?php

namespace Oka\AttachmentManagerBundle\EventListener;

use Doctrine\ORM\Events;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Oka\AttachmentManagerBundle\Model\AbstractDoctrineListener;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class DoctrineORMListener extends AbstractDoctrineListener
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
        if (!$classMetadata instanceof \Doctrine\ORM\Mapping\ClassMetadata) {
            return;
        }

        $classMetadata->mapManyToMany([
            'fieldName' => 'attachments',
            'targetEntity' => $this->className,
            'fetch' => \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EXTRA_LAZY,
            'joinTable' => [
                'name' => sprintf('%s_attachment', $classMetadata->getTableName()),
                'joinColumns' => [
                    [
                        'name' => sprintf('%s_id', $classMetadata->getTableName()),
                        'referencedColumnName' => 'id',
                        'onDelete' => 'CASCADE',
                        'nullable' => false,
                    ],
                ],
                'inverseJoinColumns' => [
                    [
                        'name' => 'attachment_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'CASCADE',
                        'nullable' => false,
                    ],
                ],
            ],
        ]);
    }
}
