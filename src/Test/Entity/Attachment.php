<?php

namespace Oka\AttachmentManagerBundle\Test\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oka\AttachmentManagerBundle\Model\AbstractAttachment;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="attachment")
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class Attachment extends AbstractAttachment
{
    /**
     * @ORM\Id()
     *
     * @ORM\Column(type="integer")
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"summary", "details"})
     *
     * @var string
     */
    protected $id;

    public function getId(): string
    {
        return (string) $this->id;
    }
}
