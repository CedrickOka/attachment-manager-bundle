<?php

namespace Oka\AttachmentManagerBundle\Model;

use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Oka\AttachmentManagerBundle\Reflection\ClassAnalyzer;
use Oka\AttachmentManagerBundle\Service\VolumeHandlerManager;
use Oka\AttachmentManagerBundle\Traits\Attacheable;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
abstract class AbstractDoctrineListener implements EventSubscriber
{
    /**
     * @var ClassAnalyzer
     */
    protected $classAnalyser;

    public function __construct(protected string $className, protected VolumeHandlerManager $volumeHandlerManager)
    {
        $this->classAnalyser = new ClassAnalyzer();
    }

    public function getSubscribedEvents(): array
    {
        return [
            'preRemove',
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $event->getClassMetadata();

        /** @var \ReflectionClass $reflClass */
        if (null === ($reflClass = $classMetadata->getReflectionClass())) {
            return;
        }

        if (!$this->classAnalyser->hasTrait($reflClass, Attacheable::class, true)) {
            return;
        }

        $this->doLoadClassMetadata($classMetadata, $reflClass);
    }

    public function preRemove(LifecycleEventArgs $event): void
    {
        if (!is_a($event->getObject(), $this->className)) {
            return;
        }

        $this->volumeHandlerManager->deleteFile($event->getObject());
    }

    abstract protected function doLoadClassMetadata(ClassMetadata $classMetadata, \ReflectionClass $reflClass): void;
}
