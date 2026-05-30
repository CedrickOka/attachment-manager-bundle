<?php

namespace Oka\AttachmentManagerBundle\Volume;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class Volume
{
    public function __construct(
        private string $name,
        private string $dsn,
        private array $options = [],
        private ?string $publicUrl = null,
        private int $cacheItemTtl = 0,
    ) {
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

    public function getCacheItemTtl(): int
    {
        return $this->cacheItemTtl;
    }
}
