<?php
namespace Oka\AttachmentManagerBundle\Model;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
interface AttachmentRelatedObjectInterface
{
    public function getClassName(): string;
    
    public function setClassName(string $className): self;
        
    public function getIdentifier(): string;
    
    public function setIdentifier(string $identifier): self;
}
