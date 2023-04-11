<?php
namespace Oka\AttachmentManagerBundle\Tests\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Oka\AttachmentManagerBundle\Traits\AttacheableDocument;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 * @MongoDB\Document(collection="acme")
 */
class Acme
{
    use AttacheableDocument;
    
    /**
     * @MongoDB\Id()
     *
     * @var string
     */
    protected $id;
    
    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }
    
    public function getId(): string
    {
        return $this->id;
    }
}
