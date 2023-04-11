<?php
namespace Oka\AttachmentManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Oka\InputHandlerBundle\Annotation\AccessControl;
use Oka\InputHandlerBundle\Annotation\RequestContent;
use Symfony\Component\Serializer\SerializerInterface;
use Oka\AttachmentManagerBundle\Service\VolumeHandlerManager;
use Oka\AttachmentManagerBundle\Model\AbstractController;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class VolumeController extends AbstractController
{
    private $volumeHandlerManager;
    
    public function __construct(SerializerInterface $serializer, VolumeHandlerManager $volumeHandlerManager)
    {
        parent::__construct($serializer);
        
        $this->volumeHandlerManager = $volumeHandlerManager;
    }
    
    /**
     * Create volume.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     * @RequestContent(constraints="createConstraints")
     */
    public function create(Request $request, $version, $protocol, array $requestContent): Response
    {
        if ($this->volumeHandlerManager->exists($requestContent['name'])) {
            throw new ConflictHttpException(sprintf(sprintf('Volume with name "%s" already exists.', $requestContent['name'])));
        }
        
        $volume = $this->volumeHandlerManager->create($requestContent['name']);
        
        return $this->json($volume, 201, [], [AbstractObjectNormalizer::GROUPS => []]);
    }
    
    /**
     * Delete volume.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     */
    public function delete(Request $request, $version, $protocol, string $name): Response
    {
        $this->volumeHandlerManager->delete($name, $request->query->has('recursive'));
        
        return new JsonResponse(null, 204);
    }
    
    private static function createConstraints(): Assert\Collection
    {
        return new Assert\Collection([
            'name' => new Assert\Required(new Assert\NotBlank()),
        ]);
    }
}
