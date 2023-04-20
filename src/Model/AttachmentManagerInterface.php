<?php

namespace Oka\AttachmentManagerBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
interface AttachmentManagerInterface
{
    public function create(string $relatedObjectName, string $relatedObjectIdentifier, UploadedFile $uploadedFile, array $metadata = []): AttachmentInterface;

    public function update(AttachmentInterface $attachment, UploadedFile $uploadedFile, array $metadata = []): AttachmentInterface;

    public function delete(AttachmentInterface $attachment): void;

    public function find($id): ?AttachmentInterface;

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): array;

    public function findOneBy(array $criteria, array $orderBy = null): array;

    public function findAll(array $orderBy = null): array;
}
