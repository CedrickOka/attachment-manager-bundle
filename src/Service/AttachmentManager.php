<?php

namespace Oka\AttachmentManagerBundle\Service;

use Doctrine\Persistence\ObjectManager;
use Oka\AttachmentManagerBundle\Event\UploadedFileEvent;
use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Oka\AttachmentManagerBundle\Model\AttachmentManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class AttachmentManager implements AttachmentManagerInterface
{
    private $prefixSeparator;
    private $className;
    private $relatedObjets;
    private $objectManager;
    private $volumeHandlerManager;
    private $dispatcher;

    /**
     * @var \Doctrine\Persistence\ObjectRepository
     */
    private $objectRepository;

    public function __construct(
        string $prefixSeparator,
        string $className,
        array $relatedObjets,
        ObjectManager $objectManager,
        VolumeHandlerManager $volumeHandlerManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->prefixSeparator = $prefixSeparator;
        $this->className = $className;
        $this->relatedObjets = new ParameterBag($relatedObjets);
        $this->objectManager = $objectManager;
        $this->volumeHandlerManager = $volumeHandlerManager;
        $this->dispatcher = $dispatcher;
        $this->objectRepository = $objectManager->getRepository($className);
    }

    public function create(string $relatedObjectName, string $relatedObjectIdentifier, File $file, array $metadata = []): AttachmentInterface
    {
        if (!$this->relatedObjets->has($relatedObjectName)) {
            throw new \InvalidArgumentException(sprintf('The related object with the name "%s" does not exist.', $relatedObjectName));
        }

        $relatedObjectConfig = $this->relatedObjets->get($relatedObjectName);

        /** @var \Oka\AttachmentManagerBundle\Traits\Attacheable $relatedObject */
        if (!$relatedObject = $this->objectManager->find($relatedObjectConfig['class'], $relatedObjectIdentifier)) {
            throw new \InvalidArgumentException(sprintf('The related object with the identifier "%s" is not found.', $relatedObjectIdentifier));
        }

        /** @var AttachmentInterface $attachment */
        $attachment = new $this->className();
        $attachment->setVolumeName($relatedObjectConfig['volume_used']);
        $attachment->setMetadata($metadata);

        $mimeTypes = new MimeTypes();
        $extensions = $mimeTypes->getExtensions($file->getMimeType());
        $attachment->setFilename(sprintf(
            '%s%s%s%s%s',
            $relatedObjectConfig['directory'] ?? $relatedObjectIdentifier,
            \DIRECTORY_SEPARATOR,
            isset($relatedObjectConfig['prefix']) ? sprintf('%s%s', $relatedObjectConfig['prefix'], $this->prefixSeparator) : '',
            Uuid::v4()->__toString(),
            isset($extensions[0]) ? '.'.$extensions[0] : ''
        ));

        if (false === $this->objectManager->contains($attachment)) {
            $this->objectManager->persist($attachment);
        }

        $relatedObject->addAttachment($attachment);

        if (!$this->volumeHandlerManager->exists($relatedObjectConfig['volume_used'])) {
            $this->volumeHandlerManager->create($relatedObjectConfig['volume_used']);
        }

        /** @var UploadedFileEvent $event */
        $event = $this->dispatcher->dispatch(new UploadedFileEvent($attachment, $file));

        $this->volumeHandlerManager->putFile($attachment, $event->getUploadedFile());

        $attachment->setLastModified();
        $this->objectManager->flush();

        return $attachment;
    }

    public function update(AttachmentInterface $attachment, File $file = null, array $metadata = []): AttachmentInterface
    {
        if (!empty($metadata)) {
            $attachment->setMetadata($metadata);
        }

        if (null !== $file) {
            if (!$this->volumeHandlerManager->exists($attachment->getVolumeName())) {
                $this->volumeHandlerManager->create($attachment->getVolumeName());
            }

            /** @var UploadedFileEvent $event */
            $event = $this->dispatcher->dispatch(new UploadedFileEvent($attachment, $file));

            $this->volumeHandlerManager->putFile($attachment, $event->getUploadedFile());
        }

        $attachment->setLastModified();
        $this->objectManager->flush();

        return $attachment;
    }

    public function delete(AttachmentInterface $attachment): void
    {
        $this->volumeHandlerManager->deleteFile($attachment);
        $this->objectManager->remove($attachment);
        $this->objectManager->flush();
    }

    public function find($id): ?AttachmentInterface
    {
        return $this->objectRepository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): array
    {
        return $this->objectRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria, array $orderBy = null): array
    {
        return $this->findBy($criteria, $orderBy, 1, 0);
    }

    public function findAll(array $orderBy = null): array
    {
        return $this->findBy([], $orderBy);
    }
}
