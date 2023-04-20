<?php

namespace Oka\AttachmentManagerBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 * @Annotation
 */
#[\Attribute]
class IsRelatedObjectName extends Constraint
{
    public string $message = 'The value you selected is not a valid choice.';
    public string $errorPath = '[relatedObject][name]';
}
