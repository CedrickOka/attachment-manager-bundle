<?php

namespace Oka\AttachmentManagerBundle\Event;

use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\Event;

class UploadedFileEvent extends Event
{
    private $attachment;
    private $uploadedFile;

    public function __construct(AttachmentInterface $attachment, UploadedFile $uploadedFile)
    {
        $this->attachment = $attachment;
        $this->uploadedFile = $uploadedFile;
    }

    public function getAttachment(): AttachmentInterface
    {
        return $this->attachment;
    }

    public function getUploadedFile(): UploadedFile
    {
        return $this->uploadedFile;
    }

    public function setUploadedFile(UploadedFile $uploadedFile): self
    {
        $this->uploadedFile = $uploadedFile;

        return $this;
    }
}
