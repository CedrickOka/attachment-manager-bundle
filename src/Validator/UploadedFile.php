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
        public $relatedObjectName,
        public ?string $relatedObjectIdentifier = null,
        public ?string $errorPath = null,
        public string $message = 'The file is not valid.',
        ?array $groups = null,
        $payload = null,
        array $options = [],
    ) {
        if (\is_array($relatedObjectName)) {
            $options = array_merge($relatedObjectName, $options);
        } elseif (null !== $relatedObjectName) {
            $options['relatedObjectName'] = $relatedObjectName;
        }

        parent::__construct($options, $groups, $payload);
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
