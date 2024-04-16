<?php

namespace Oka\AttachmentManagerBundle\Event;

use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\EventDispatcher\Event;

class UploadedFileEvent extends Event
{
    private $attachment;
    private $file;

    public function __construct(AttachmentInterface $attachment, File $file, mixed $relatedObject = null)
    {
        $this->attachment = $attachment;
        $this->file = $file;
        $this->relatedObject = $relatedObject;
    }

    public function getAttachment(): AttachmentInterface
    {
        return $this->attachment;
    }

    public function getUploadedFile(): File
    {
        return $this->file;
    }

    public function setUploadedFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }
    
    public function getRelatedObject(): ?mixed
    {
        return $this->relatedObject;
    }
}
