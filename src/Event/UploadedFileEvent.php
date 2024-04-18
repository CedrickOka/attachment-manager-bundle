<?php

namespace Oka\AttachmentManagerBundle\Event;

use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\EventDispatcher\Event;

class UploadedFileEvent extends Event
{
    private $attachment;
    private $uploadedFile;
    private $relatedObject;

    public function __construct(AttachmentInterface $attachment, File $uploadedFile, mixed $relatedObject)
    {
        $this->attachment = $attachment;
        $this->uploadedFile = $uploadedFile;
        $this->relatedObject = $relatedObject;
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
