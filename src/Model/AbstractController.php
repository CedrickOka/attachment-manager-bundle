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
    public function __construct(private SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    protected function json($data, int $statusCode = 200, array $headers = [], array $context = []): JsonResponse
    {
        $context = [
            AbstractObjectNormalizer::GROUPS => ['details'],
            ...$context,
        ];

        return new JsonResponse($this->serializer->serialize($data, 'json', $context), $statusCode, $headers, true);
    }
}
