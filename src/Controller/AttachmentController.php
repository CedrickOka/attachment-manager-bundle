<?php
namespace Oka\AttachmentManagerBundle\Controller;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Oka\AttachmentManagerBundle\Model\AttachmentManagerInterface;
use Oka\InputHandlerBundle\Annotation\AccessControl;
use Oka\InputHandlerBundle\Annotation\RequestContent;
use Symfony\Component\Serializer\SerializerInterface;
use Oka\AttachmentManagerBundle\Model\AbstractController;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class AttachmentController extends AbstractController
{
    private $attachmentManagerLocator;
    private $relatedObjectDBDriverMapping;
    
    public function __construct(SerializerInterface $serializer, ServiceLocator $attachmentManagerLocator, array $relatedObjectDBDriverMapping)
    {
        parent::__construct($serializer);
        
        $this->attachmentManagerLocator = $attachmentManagerLocator;
        $this->relatedObjectDBDriverMapping = $relatedObjectDBDriverMapping;
    }
    
    /**
     * Create attachment.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     * @RequestContent(constraints="createConstraints")
     */
    public function create(Request $request, $version, $protocol, array $requestContent): Response
    {
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
     * @RequestContent(constraints="updateConstraints")
     */
    public function update(Request $request, $version, $protocol, array $requestContent, string $id, string $relatedObjectName): JsonResponse
    {
        $attachmentManager = $this->getAttachmentManager($relatedObjectName);
        
        if (!$attachment = $attachmentManager->find($id)) {
            throw new NotFoundHttpException(sprintf('Attachment with resource identifier "%s" is not found.', $id));
        }
        
        $attachmentManager->update($attachment, $requestContent['file'], $requestContent['metadata'] ?? []);
        
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
    
    private static function createConstraints(): Assert\Collection
    {
        return new Assert\Collection([
            'relatedObject' => new Assert\Required(new Assert\Collection([
                'name' => new Assert\Required(new Assert\Choice(['acme_orm', 'acme_mongodb'])),
                'identifier' => new Assert\Required(new Assert\NotBlank()),
            ])),
            'file' => new Assert\Required(new Assert\File(['maxSize' => '10M'])),
            'metadata' => new Assert\Optional(new Assert\Collection([
                'fields' => [],
                'allowExtraFields' => true,
            ])),
        ]);
    }
    
    private static function updateConstraints(): Assert\Collection
    {
        $constraints = self::createConstraints();
        unset($constraints->fields['relatedObject']);
        
        return $constraints;
    }
}
