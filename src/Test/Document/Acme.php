<?php

namespace Oka\AttachmentManagerBundle\Test\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Oka\AttachmentManagerBundle\Traits\Attacheable;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
#[MongoDB\Document(collection: 'acme')]
class Acme
{
    use Attacheable;

    /**
     * @var string
     */
    #[MongoDB\Id()]
    #[Serializer\Groups(['summary', 'details'])]
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
