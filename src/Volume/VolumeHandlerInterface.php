<?php

namespace Oka\AttachmentManagerBundle\Volume;

use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
interface VolumeHandlerInterface
{
    public function exists(Volume $volume): bool;

    public function create(Volume $volume): Volume;

    public function delete(Volume $volume, bool $recursive = false): void;

    public function putFile(Volume $volume, AttachmentInterface $attachment, File $file): void;

    //     public function createFileUploadForm(Volume $volume, string $filename, array $metadata, string $expiresAt = null): FileUploadForm;

    public function getFileInfo(Volume $volume, AttachmentInterface $attachment): FileInfo;

    public function deleteFile(Volume $volume, AttachmentInterface $attachment): void;

    public function getFilePublicUrl(Volume $volume, AttachmentInterface $attachment): string;
}
