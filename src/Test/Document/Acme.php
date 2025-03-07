<?php

namespace Oka\AttachmentManagerBundle\Test\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Oka\AttachmentManagerBundle\Traits\Attacheable;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 * @MongoDB\Document(collection="acme")
 */
class Acme
{
    use Attacheable;

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
