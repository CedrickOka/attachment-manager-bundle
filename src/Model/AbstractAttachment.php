<?php

namespace Oka\AttachmentManagerBundle\Model;

use Oka\AttachmentManagerBundle\Volume\FileInfo;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
abstract class AbstractAttachment implements AttachmentInterface
{
    /**
     * @var string
     */
    protected $volumeName;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $metadata;

    /**
     * @var \DateTimeInterface
     */
    protected $lastModified;
    
    /**
     * @var \Oka\AttachmentManagerBundle\Volume\FileInfo
     */
    protected $fileInfo;

    public function __construct()
    {
        $this->metadata = [];
    }

    public function getVolumeName(): string
    {
        return $this->volumeName;
    }

    public function setVolumeName(string $volumeName): self
    {
        $this->volumeName = $volumeName;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getLastModified(): \DateTimeInterface
    {
        return $this->lastModified;
    }

    public function setLastModified(\DateTimeInterface $lastModified): self
    {
        $this->lastModified = $lastModified;

        return $this;
    }
    
    public function getFileInfo(): FileInfo
    {
        return $this->fileInfo;
    }
    
    public function setFileInfo(FileInfo $fileInfo): self
    {
        $this->fileInfo = $fileInfo;
        
        return $this;
    }

    public function prePersist(): void
    {
        $this->lastModified = new \DateTime();
    }
}
