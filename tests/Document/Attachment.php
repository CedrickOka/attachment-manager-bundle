<?php

namespace Oka\AttachmentManagerBundle\Tests\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Oka\AttachmentManagerBundle\Model\AbstractAttachment;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 * @MongoDB\Document(collection="attachment")
 */
class Attachment extends AbstractAttachment
{
    /**
     * @MongoDB\Id()
     *
     * @Serializer\Groups({"summary", "details"})
     *
     * @var string
     */
    protected $id;

    public function getId(): string
    {
        return $this->id;
    }
}
