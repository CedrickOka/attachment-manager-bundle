<?php

namespace Oka\AttachmentManagerBundle\Model;

use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
interface AttachmentManagerInterface
{
    public function create(string $relatedObjectName, string|object $relatedObjectIdentifier, File $file, array $metadata = [], bool $andFlush = true): AttachmentInterface;

    public function update(AttachmentInterface $attachment, File $file, array $metadata = [], bool $andFlush = true): AttachmentInterface;

    public function delete(AttachmentInterface $attachment, bool $andFlush = true): void;

    public function find(string|int $id): ?AttachmentInterface;

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function findOneBy(array $criteria, ?array $orderBy = null): array;

    public function findAll(?array $orderBy = null): array;

    public function findRelatedObjectByAttachmentId(string $relatedObjectName, string $attachmentId): ?object;

    public function getClassName(): string;

    public function getRelatedObjets(): ParameterBag;

    public function getObjectManager(): ObjectManager;
}
