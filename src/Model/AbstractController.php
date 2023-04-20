<?php

namespace Oka\AttachmentManagerBundle\Model;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
abstract class AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    protected function json($data, int $statusCode = 200, array $headers = [], array $context = []): JsonResponse
    {
        $context = [
            AbstractObjectNormalizer::GROUPS => ['details'],
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            ...$context,
        ];

        return new JsonResponse($this->serializer->serialize($data, 'json', $context), $statusCode, $headers, true);
    }
}
