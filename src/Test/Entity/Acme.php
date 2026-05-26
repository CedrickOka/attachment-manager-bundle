<?php

namespace Oka\AttachmentManagerBundle\Test\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oka\AttachmentManagerBundle\Traits\Attacheable;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
#[ORM\Entity()]
#[ORM\Table(name: 'acme')]
class Acme
{
    use Attacheable;

    /**
     * @var int
     */
    #[ORM\Id()]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Serializer\Groups(['summary', 'details'])]
    protected $id;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }
}
