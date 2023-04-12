<?php
namespace Oka\AttachmentManagerBundle\Model;

use Doctrine\Common\EventSubscriber;
use Oka\AttachmentManagerBundle\Reflection\ClassAnalyzer;
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
        return $this->classAnalyser->hasTrait($reflClass, Attacheable::class, $recursive);
    }
}
