<?php

namespace Oka\AttachmentManagerBundle\Test\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oka\AttachmentManagerBundle\Model\AbstractAttachment;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
#[ORM\Entity()]
#[ORM\Table(name: 'attachment')]
class Attachment extends AbstractAttachment
{
    /**
     * @var int
     */
    #[ORM\Id()]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Serializer\Groups(['summary', 'details'])]
    protected $id;

    public function getId(): int
    {
        return $this->id;
    }
}
