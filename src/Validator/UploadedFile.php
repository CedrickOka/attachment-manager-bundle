<?php
namespace Oka\AttachmentManagerBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 * @Annotation
 */
#[\Attribute]
class UploadedFile extends Constraint
{
    public ?string $relatedObjectName = null;
    public ?string $errorPath = null;
    public string $message = 'The file is not valid.';
    
    public function getRequiredOptions()
    {
        return ['relatedObjectName'];
    }
    
    public function getDefaultOption()
    {
        return 'relatedObjectName';
    }
}
