<?php

namespace Oka\AttachmentManagerBundle\Volume;

use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class FileVolumeHandler implements VolumeHandlerInterface
{
    public function exists(Volume $volume): bool
    {
        return file_exists($volume->getDsn());
    }

    public function create(Volume $volume): Volume
    {
        mkdir($volume->getDsn(), 0755, true);

        return $volume;
    }

    public function delete(Volume $volume, bool $recursive = false): void
    {
        if (true === $recursive) {
            $files = scandir($volume->getDsn());

            foreach ($files as $file) {
                if ('.' === $file || '..' === $file) {
                    continue;
                }

                $path = sprintf('%s%s%s', $volume->getDsn(), \DIRECTORY_SEPARATOR, $file);
                is_dir($path) ? delete($path, $recursive) : unlink($path);
            }
        }

        rmdir($volume->getDsn());
    }

    public function putFile(Volume $volume, AttachmentInterface $attachment, File $file): void
    {
        $originalName = str_replace('\\', '/', $attachment->getFilename());
        $position = strrpos($originalName, '/');
        $originalName = false === $position ? $originalName : substr($originalName, $position + 1);

        $file->move(
            false === $position ? $volume->getDsn() : sprintf('%s%s%s', $volume->getDsn(), \DIRECTORY_SEPARATOR, substr($attachment->getFilename(), 0, $position)),
            $originalName
        );
    }

    public function getFileInfo(Volume $volume, AttachmentInterface $attachment): FileInfo
    {
        $realPath = $this->getAttachmentRealPath($volume, $attachment);
        
        return new FileInfo(
            $attachment->getFilename(),
            $volume->getDsn(),
            filesize($realPath),
            mime_content_type($realPath),
            $realPath,
            $this->getFilePublicUrl($volume, $attachment)
        );
    }

    public function deleteFile(Volume $volume, AttachmentInterface $attachment): void
    {
        unlink($this->getAttachmentRealPath($volume, $attachment));
    }

    public function getFilePublicUrl(Volume $volume, AttachmentInterface $attachment): string
    {
        return sprintf('%s%s%s', $volume->getPublicUrl() ?? '', \DIRECTORY_SEPARATOR, $attachment->getFilename());
    }

    protected function getAttachmentRealPath(Volume $volume, AttachmentInterface $attachment): string
    {
        return sprintf('%s%s%s', $volume->getDsn(), \DIRECTORY_SEPARATOR, $attachment->getFilename());
    }
}
