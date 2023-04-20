<?php

namespace Oka\AttachmentManagerBundle\Volume;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class FileInfo
{
    private $name;
    private $path;
    private $realPath;
    private $publicUrl;

    public function __construct(string $name, string $path, string $realPath, string $publicUrl)
    {
        $this->name = $name;
        $this->path = $path;
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

    public function getRealPath(): string
    {
        return $this->realPath;
    }

    public function getPublicUrl(): string
    {
        return $this->publicUrl;
    }
}
