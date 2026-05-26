<?php

namespace Oka\AttachmentManagerBundle\Test\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Oka\AttachmentManagerBundle\Model\AbstractAttachment;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
#[MongoDB\Document(collection: 'attachment')]
class Attachment extends AbstractAttachment
{
    /**
     * @var string
     */
    #[MongoDB\Id()]
    #[Serializer\Groups(['summary', 'details'])]
    protected $id;

    public function getId(): string
    {
        return $this->id;
    }
}
