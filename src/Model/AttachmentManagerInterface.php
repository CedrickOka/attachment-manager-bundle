<?php

namespace Oka\AttachmentManagerBundle\Model;

use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
interface AttachmentManagerInterface
{
    public function create(string $relatedObjectName, string $relatedObjectIdentifier, File $file, array $metadata = [], bool $andFlush = true): AttachmentInterface;

    public function update(AttachmentInterface $attachment, File $file, array $metadata = [], bool $andFlush = true): AttachmentInterface;

    public function delete(AttachmentInterface $attachment, bool $andFlush = true): void;

    public function find($id): ?AttachmentInterface;

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function findOneBy(array $criteria, ?array $orderBy = null): array;

    public function findAll(?array $orderBy = null): array;
}
