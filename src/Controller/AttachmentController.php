<?php

namespace Oka\AttachmentManagerBundle\Controller;

use Oka\AttachmentManagerBundle\Model\AbstractController;
use Oka\AttachmentManagerBundle\Model\AttachmentManagerInterface;
use Oka\AttachmentManagerBundle\Validator\IsRelatedObjectName;
use Oka\AttachmentManagerBundle\Validator\UploadedFile;
use Oka\InputHandlerBundle\Annotation\AccessControl;
use Oka\InputHandlerBundle\Annotation\RequestContent;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class AttachmentController extends AbstractController
{
    public function __construct(
        SerializerInterface $serializer, 
        private ValidatorInterface $validator, 
        private ServiceLocator $attachmentManagerLocator, 
        private array $relatedObjectDBDriverMapping
    ) {
        parent::__construct($serializer);
    }

    /**
     * Create attachment.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     *
     * @RequestContent(constraints="createConstraints")
     */
    public function create(Request $request, $version, $protocol, array $requestContent): Response
    {
        $constraint = new UploadedFile([
            'errorPath' => '[file]', 
            'relatedObjectName' => $requestContent['relatedObject']['name'], 
            'relatedObjectIdentifier' => $requestContent['relatedObject']['identifier'],
        ]);

        if (null !== ($response = $this->validate($requestContent['file'], $constraint))) {
            return $response;
        }

        $attachment = $this->getAttachmentManager($requestContent['relatedObject']['name'])
                           ->create(
                               $requestContent['relatedObject']['name'],
                               $requestContent['relatedObject']['identifier'],
                               $requestContent['file'],
                               $requestContent['metadata'] ?? []
                           );

        return $this->json($attachment, 201);
    }

    /**
     * Show attachment details.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     */
    public function read(Request $request, $version, $protocol, string $id, string $relatedObjectName): JsonResponse
    {
        if (!$attachment = $this->getAttachmentManager($relatedObjectName)->find($id)) {
            throw new NotFoundHttpException(sprintf('Attachment with resource identifier "%s" is not found.', $id));
        }

        return $this->json($attachment);
    }

    /**
     * Update attachment.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     *
     * @RequestContent(constraints="updateConstraints")
     */
    public function update(Request $request, $version, $protocol, array $requestContent, string $id, string $relatedObjectName): JsonResponse
    {
        if (isset($requestContent['file'])) {
            if (null !== ($response = $this->validate($requestContent['file'], new UploadedFile(['relatedObjectName' => $relatedObjectName, 'errorPath' => '[file]'])))) {
                return $response;
            }
        }

        $attachmentManager = $this->getAttachmentManager($relatedObjectName);

        if (!$attachment = $attachmentManager->find($id)) {
            throw new NotFoundHttpException(sprintf('Attachment with resource identifier "%s" is not found.', $id));
        }

        $attachmentManager->update($attachment, $requestContent['file'] ?? null, $requestContent['metadata'] ?? []);

        return $this->json($attachment);
    }

    /**
     * Delete attachment.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     */
    public function delete(Request $request, $version, $protocol, string $id, string $relatedObjectName): Response
    {
        $attachmentManager = $this->getAttachmentManager($relatedObjectName);

        if (!$attachment = $attachmentManager->find($id)) {
            throw new NotFoundHttpException(sprintf('Attachment with resource identifier "%s" is not found.', $id));
        }

        $attachmentManager->delete($attachment);

        return new JsonResponse(null, 204);
    }

    private function getAttachmentManager(string $relatedObjectName): AttachmentManagerInterface
    {
        return $this->attachmentManagerLocator->get($this->relatedObjectDBDriverMapping[$relatedObjectName]);
    }

    private function validate($data, ?Constraint $constraint = null, array $groups = []): ?Response
    {
        /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $errors */
        $errors = $this->validator->validate($data, $constraint, $groups);

        return $errors->count() > 0 ? $this->json($errors, 400) : null;
    }

    private static function createConstraints(): Assert\Collection
    {
        return self::itemConstraints();
    }

    private static function updateConstraints(): Assert\Collection
    {
        $constraints = self::itemConstraints(false);
        unset($constraints->fields['relatedObject']);

        return $constraints;
    }

    private static function itemConstraints(bool $required = true): Assert\Collection
    {
        $className = true === $required ? Assert\Required::class : Assert\Optional::class;

        return new Assert\Collection([
            'relatedObject' => new Assert\Required(new Assert\Collection([
                'name' => new Assert\Required(new IsRelatedObjectName()),
                'identifier' => new Assert\Required(new Assert\NotBlank()),
            ])),
            'file' => new $className(new Assert\File()),
            'metadata' => new Assert\Optional(new Assert\Collection([
                'fields' => [],
                'allowExtraFields' => true,
            ])),
        ]);
    }
}
