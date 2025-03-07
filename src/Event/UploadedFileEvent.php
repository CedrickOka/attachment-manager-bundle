<?php

namespace Oka\AttachmentManagerBundle\Event;

use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\EventDispatcher\Event;

class UploadedFileEvent extends Event
{
    public function __construct(private AttachmentInterface $attachment, private File $uploadedFile, private mixed $relatedObject = null)
    {
    }

    public function getAttachment(): AttachmentInterface
    {
        return $this->attachment;
    }

    public function getUploadedFile(): File
    {
        return $this->uploadedFile;
    }

    public function setUploadedFile(File $uploadedFile): self
    {
        $this->uploadedFile = $uploadedFile;

        return $this;
    }

    public function getRelatedObject(): mixed
    {
        return $this->relatedObject;
    }
}
