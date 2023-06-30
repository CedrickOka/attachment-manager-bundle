<?php

namespace Oka\AttachmentManagerBundle\Volume;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class FileInfo
{
    private $name;
    private $path;
    private $size;
    private $mimeType;
    private $realPath;
    private $publicUrl;

    public function __construct(string $name, string $path, int $size, string $mimeType, string $realPath, string $publicUrl)
    {
        $this->name = $name;
        $this->path = $path;
        $this->size = $size;
        $this->mimeType = $mimeType;
        $this->realPath = $realPath;
        $this->publicUrl = $publicUrl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }
    
    public function getSize(): int
    {
        return $this->size;
    }
    
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getRealPath(): string
    {
        return $this->realPath;
    }

    public function getPublicUrl(): string
    {
        return $this->publicUrl;
    }
}
