<?php

namespace Oka\AttachmentManagerBundle\Model;

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

    public function hasMeta(string $name): bool
    {
        return isset($this->metadata[$name]);
    }

    public function getMeta(string $name, mixed $defaultValue = null): mixed
    {
        return $this->metadata[$name] ?? $defaultValue;
    }

    public function putMeta(string $name, mixed $value): self
    {
        $this->metadata[$name] = $value;

        return $this;
    }

    public function removeMeta(string $name): self
    {
        unset($this->metadata[$name]);

        return $this;
    }

    public function getLastModified(): \DateTimeInterface
    {
        return $this->lastModified;
    }

    public function setLastModified(?\DateTimeInterface $lastModified = null): self
    {
        $this->lastModified = $lastModified ?? new \DateTime();

        return $this;
    }
}
