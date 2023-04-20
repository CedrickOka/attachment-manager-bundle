<?php

namespace Oka\AttachmentManagerBundle\Model;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
interface AttachmentInterface
{
    public function getVolumeName(): string;

    public function setVolumeName(string $volumeName): self;

    public function getFilename(): string;

    public function setFilename(string $filename): self;

    public function getMetadata(): array;

    public function setMetadata(array $metadata): self;

    public function getLastModified(): \DateTimeInterface;

    public function setLastModified(\DateTimeInterface $lastModified): self;
}
