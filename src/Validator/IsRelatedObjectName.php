<?php

namespace Oka\AttachmentManagerBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
#[\Attribute()]
class IsRelatedObjectName extends Constraint
{
    public function __construct(
        public string $message = 'The value you selected is not a valid choice.',
        public string $errorPath = '[relatedObject][name]',
        ?array $groups = null,
        $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }
}
