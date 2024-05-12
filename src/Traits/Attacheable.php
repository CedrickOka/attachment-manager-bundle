<?php

namespace Oka\AttachmentManagerBundle\Traits;

use Doctrine\Common\Collections\Collection;
use Oka\AttachmentManagerBundle\Model\AttachmentInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
trait Attacheable
{
    /**
     * @var Collection
     */
    protected $attachments;

    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(AttachmentInterface $attachment): self
    {
        if (false === $this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
        }

        return $this;
    }

    public function setAttachments(iterable $attachments): self
    {
        $this->attachments = $attachments;

        return $this;
    }

    public function removeAttachment(AttachmentInterface $attachment): self
    {
        $foundAttachment = $this->attachments->findFirst(function (int $key, AttachmentInterface $value) use ($attachment): bool {
            return $value->getVolumeName() === $attachment->getVolumeName() && $value->getFilename() === $attachment->getFilename();
        });

        if (null !== $foundAttachment) {
            $this->attachments->removeElement($this->attachments->indexOf($foundAttachment));
        }

        return $this;
    }
}
