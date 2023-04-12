<?php
namespace Oka\AttachmentManagerBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class IsRelatedObjectNameValidator extends ConstraintValidator
{
    private $relatedObjectNames;
    
    public function __construct(array $relatedObjectNames)
    {
        $this->relatedObjectNames = $relatedObjectNames;
    }
    
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsRelatedObjectName) {
            throw new UnexpectedTypeException($constraint, IsRelatedObjectName::class);
        }
        
        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }
        
        if (!is_string($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'string');
        }
        
        if (!in_array($value, $this->relatedObjectNames)) {
            $this->context->buildViolation($constraint->message, [])
                          ->setParameter('{{ string }}', $value)
                          ->atPath($constraint->errorPath)
                          ->setInvalidValue($value)
                          ->addViolation();
        }
    }
}
