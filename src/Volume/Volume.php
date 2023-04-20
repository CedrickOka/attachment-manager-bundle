<?php

namespace Oka\AttachmentManagerBundle\Volume;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class Volume
{
    private $name;
    private $dsn;
    private $options;
    private $publicUrl;

    public function __construct(string $name, string $dsn, array $options = [], string $publicUrl = null)
    {
        $this->name = $name;
        $this->dsn = $dsn;
        $this->options = $options;
        $this->publicUrl = $publicUrl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDsn(): string
    {
        return $this->dsn;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getPublicUrl(): ?string
    {
        return $this->publicUrl;
    }
}
