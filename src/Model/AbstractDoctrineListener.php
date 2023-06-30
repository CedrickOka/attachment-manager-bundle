<?php

namespace Oka\AttachmentManagerBundle\Model;

use Doctrine\Common\EventSubscriber;
use Oka\AttachmentManagerBundle\Reflection\ClassAnalyzer;
use Oka\AttachmentManagerBundle\Traits\Attacheable;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oka\AttachmentManagerBundle\Service\VolumeHandlerManager;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
abstract class AbstractDoctrineListener implements EventSubscriber
{
    protected $className;
    protected $volumeHandlerManager;

    /**
     * @var ClassAnalyzer
     */
    private $classAnalyser;

    public function __construct(string $className, VolumeHandlerManager $volumeHandlerManager)
    {
        $this->className = $className;
        $this->volumeHandlerManager = $volumeHandlerManager;
        $this->classAnalyser = new ClassAnalyzer();
    }
    
    public function postLoad(LifecycleEventArgs $eventArgs): void
    {
        $object = $eventArgs->getObject();

        if (!$object instanceof AttachmentInterface) {
            return;
        }

        $object->setFileInfo($this->volumeHandlerManager->getFileInfo($object->getVolumeName(), $object));
    }

    protected function isObjectSupported(\ReflectionClass $reflClass, bool $recursive = true): bool
    {
        return $this->classAnalyser->hasTrait($reflClass, Attacheable::class, $recursive);
    }
}
