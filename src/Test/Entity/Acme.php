<?php

namespace Oka\AttachmentManagerBundle\Test\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oka\AttachmentManagerBundle\Traits\Attacheable;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="acme")
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class Acme
{
    use Attacheable;

    /**
     * @ORM\Id()
     *
     * @ORM\Column(type="integer")
     *
     * @ORM\GeneratedValue(strategy="AUTO")
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
        return (string) $this->id;
    }
}
