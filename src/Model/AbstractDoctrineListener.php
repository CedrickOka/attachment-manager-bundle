<?php
namespace Oka\AttachmentManagerBundle\Model;

use Oka\AttachmentManagerBundle\Reflection\ClassAnalyzer;
use Doctrine\Common\EventSubscriber;
use Oka\AttachmentManagerBundle\Traits\AttacheableEntity;
use Oka\AttachmentManagerBundle\Traits\AttacheableDocument;
use Oka\AttachmentManagerBundle\Traits\Attacheable;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
abstract class AbstractDoctrineListener implements EventSubscriber
{
    protected $className;
    
    /**
     * @var ClassAnalyzer
     */
    private $classAnalyser;
    
    public function __construct(string $className)
    {
        $this->className = $className;
        $this->classAnalyser = new ClassAnalyzer();
    }
    
    protected function isObjectSupported(\ReflectionClass $reflClass, bool $recursive = true): bool
    {
        return $this->classAnalyser->hasTrait($reflClass, Attacheable::class, $recursive) || 
                $this->classAnalyser->hasTrait($reflClass, AttacheableEntity::class, $recursive) || 
                $this->classAnalyser->hasTrait($reflClass, AttacheableDocument::class, $recursive);
    }
}
