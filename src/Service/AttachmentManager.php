<?php

namespace Oka\AttachmentManagerBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oka\AttachmentManagerBundle\Event\UploadedFileEvent;
use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Oka\AttachmentManagerBundle\Model\AttachmentManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class AttachmentManager implements AttachmentManagerInterface
{
    /**
     * @var ParameterBag
     */
    private $relatedObjets;
    
    /**
     * @var \Doctrine\Persistence\ObjectRepository
     */
    private $objectRepository;

    public function __construct(
        array $relatedObjets,
        private string $prefixSeparator,
        private string $className,
        private ObjectManager $objectManager,
        private VolumeHandlerManager $volumeHandlerManager,
        private EventDispatcherInterface $dispatcher
    ) {
        $this->relatedObjets = new ParameterBag($relatedObjets);
        $this->objectRepository = $objectManager->getRepository($className);
    }

    public function create(string $relatedObjectName, string $relatedObjectIdentifier, File $file, array $metadata = [], bool $andFlush = true): AttachmentInterface
    {
        if (!$this->relatedObjets->has($relatedObjectName)) {
            throw new \InvalidArgumentException(sprintf('The related object with the name "%s" does not exist.', $relatedObjectName));
        }

        $relatedObjectConfig = $this->relatedObjets->get($relatedObjectName);

        /** @var \Oka\AttachmentManagerBundle\Traits\Attacheable $relatedObject */
        if (!$relatedObject = $this->objectManager->find($relatedObjectConfig['class'], $relatedObjectIdentifier)) {
            throw new \InvalidArgumentException(sprintf('The related object with the identifier "%s" is not found.', $relatedObjectIdentifier));
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();
        $extension = static::getFileExtension($file);

        /** @var AttachmentInterface $attachment */
        $attachment = new $this->className();
        $attachment->setVolumeName($relatedObjectConfig['volume_used']);
        $attachment->setMetadata(['mime-type' => $file->getMimeType(), ...$metadata]);
        $attachment->setFilename(sprintf(
            '%s%s%s%s%s',
            isset($relatedObjectConfig['directory']) ? $propertyAccessor->getValue($relatedObject, $relatedObjectConfig['directory']) ?? $relatedObjectConfig['directory'] : $relatedObjectIdentifier,
            \DIRECTORY_SEPARATOR,
            isset($relatedObjectConfig['prefix']) ? sprintf('%s%s', $relatedObjectConfig['prefix'], $this->prefixSeparator) : '',
            Uuid::v4()->__toString(),
            $extension ? '.'.$extension : ''
        ));

        if (false === $this->objectManager->contains($attachment)) {
            $this->objectManager->persist($attachment);
        }

        $relatedObject->addAttachment($attachment);

        if (!$this->volumeHandlerManager->exists($relatedObjectConfig['volume_used'])) {
            $this->volumeHandlerManager->create($relatedObjectConfig['volume_used']);
        }

        /** @var UploadedFileEvent $event */
        $event = $this->dispatcher->dispatch(new UploadedFileEvent($attachment, $file, $relatedObject));

        $this->volumeHandlerManager->putFile($attachment, $event->getUploadedFile());

        $attachment->setLastModified();

        if ($andFlush) {
            $this->objectManager->flush();
        }

        return $attachment;
    }

    public function update(AttachmentInterface $attachment, ?File $file = null, array $metadata = [], bool $andFlush = true): AttachmentInterface
    {
        if (!empty($metadata)) {
            $attachment->setMetadata($metadata);
        }

        if (null !== $file) {
            if (!$this->volumeHandlerManager->exists($attachment->getVolumeName())) {
                $this->volumeHandlerManager->create($attachment->getVolumeName());
            }

            $fileExtension = static::getFileExtension($file);
            $attachment->setMetadata(array_merge($attachment->getMetadata(), ['mime-type' => $file->getMimeType()]));

            /** @var UploadedFileEvent $event */
            $event = $this->dispatcher->dispatch(new UploadedFileEvent($attachment, $file));
            $this->volumeHandlerManager->putFile($attachment, $event->getUploadedFile());

            if (null !== $fileExtension && !str_ends_with($attachment->getFilename(), $fileExtension)) {
                /** @var AttachmentInterface $from */
                $from = new $this->className();
                $from->setVolumeName($attachment->getVolumeName());
                $from->setFilename($attachment->getFilename());

                if (!preg_match('#^(.+)(\.[a-zA-Z0-9]+)$#', $from->getFilename())) {
                    $attachment->setFilename(sprintf('%s.%s', $from->getFilename(), $fileExtension));
                } else {
                    $attachment->setFilename(preg_replace(sprintf('#^(.+)(\.[a-zA-Z0-9]+)$#', \DIRECTORY_SEPARATOR), sprintf('$1.%s', $fileExtension), $from->getFilename()));
                }

                $this->volumeHandlerManager->renameFile($from, $attachment);
            }
        }

        $attachment->setLastModified();

        if ($andFlush) {
            $this->objectManager->flush();
        }

        return $attachment;
    }

    public function delete(AttachmentInterface $attachment, bool $andFlush = true): void
    {
        $this->objectManager->remove($attachment);

        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    public function find(string|int $id): ?AttachmentInterface
    {
        return $this->objectRepository->find($id);
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->objectRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): array
    {
        return $this->findBy($criteria, $orderBy, 1, 0);
    }

    public function findAll(?array $orderBy = null): array
    {
        return $this->findBy([], $orderBy);
    }

    public function findRelatedObjectById(string $relatedObjectName, string $attachmentId): ?object
    {
        $relatedObjectConfig = $this->relatedObjets->get($relatedObjectName);

        /** @var EntityManager $objectManager */
        if ($this->objectManager instanceof EntityManager) {
            $classMetadata = $this->objectManager->getClassMetadata($this->className);
            $identifierFieldName = $classMetadata->getSingleIdentifierFieldName();

            return $this->objectManager->createQueryBuilder()
                                    ->select('r')
                                    ->from($relatedObjectConfig['class'], 'r')
                                    ->innerJoin('r.attachments', 'a')
                                    ->where(sprintf('a.%s = :id', $identifierFieldName))
                                    ->setParameter('id', $attachmentId, $classMetadata->getTypeOfField($identifierFieldName))
                                    ->getQuery()
                                    ->getOneOrNullResult();
        }

        /** @var DocumentManager $objectManager */
        return $this->objectManager->createQueryBuilder()
                                ->find($relatedObjectConfig['class'])
                                ->field('attachments')->includesReferenceTo($this->find($attachmentId))
                                ->getQuery()
                                ->getSingleResult();
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getRelatedObjets(): ParameterBag
    {
        return $this->relatedObjets;
    }

    public function getObjectManager(): ObjectManager
    {
        return $this->objectManager;
    }

    public static function getFileExtension(File $file): ?string
    {
        $mimeTypes = new MimeTypes();
        $extensions = $mimeTypes->getExtensions($file->getMimeType());

        return $extensions[0] ?? null;
    }
}
