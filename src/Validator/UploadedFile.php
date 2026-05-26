<?php

namespace Oka\AttachmentManagerBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
#[\Attribute()]
class UploadedFile extends Constraint
{
    public function __construct(
        public ?string $relatedObjectName = null,
        public ?string $relatedObjectIdentifier = null,
        public ?string $errorPath = null,
        public string $message = 'The file is not valid.',
        ?array $groups = null,
        $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function getRequiredOptions(): array
    {
        return ['relatedObjectName'];
    }

    public function getDefaultOption(): string
    {
        return 'relatedObjectName';
    }
}
