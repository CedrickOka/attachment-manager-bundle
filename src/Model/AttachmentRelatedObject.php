<?php
namespace Oka\AttachmentManagerBundle\Model;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class AttachmentRelatedObject implements AttachmentRelatedObjectInterface
{
    protected $className;
    protected $identifier;
    
    public function __construct(string $className, string $identifier)
    {
        $this->className = $className;
        $this->identifier = $identifier;
    }
    
    public function getClassName(): string
    {
        return $this->className;
    }
    
    public function setClassName(string $className): self
    {
        $this->className = $className;
        
        return $this;
    }
    
    public function getIdentifier(): string
    {
        return (string) $this->identifier;
    }
    
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        
        return $this;
    }
}
