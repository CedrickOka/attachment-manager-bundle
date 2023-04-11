<?php
namespace Oka\AttachmentManagerBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Oka\AttachmentManagerBundle\Service\VolumeHandlerManager;
use Oka\AttachmentManagerBundle\Model\AttachmentInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class AttachmentNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface
{
    private $normalizer;
    private $volumeHandlerManager;
    
    public function __construct(ObjectNormalizer $normalizer, VolumeHandlerManager $volumeHandlerManager)
    {
        $this->normalizer = $normalizer;
        $this->volumeHandlerManager = $volumeHandlerManager;
    }
    
    /**
     * @param AttachmentInterface $object
     * @param array|null          $format
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $data['publicUrl'] = $this->volumeHandlerManager->getFilePublicUrl($object->getVolumeName(), $object);
        
        return $data;
    }
    
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof AttachmentInterface;
    }
    
    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }
}
