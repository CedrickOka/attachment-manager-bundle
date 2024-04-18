<?php

namespace Oka\AttachmentManagerBundle\Volume;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class FileUploadForm
{
    private $attributes;
    private $multipart;

    public function __construct(array $attributes, array $multipart)
    {
        $this->attributes = $attributes;
        $this->multipart = $multipart;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getMultipart(): array
    {
        return $this->multipart;
    }
}
