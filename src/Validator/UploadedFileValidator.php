<?php

namespace Oka\AttachmentManagerBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class UploadedFileValidator extends ConstraintValidator
{
    private $uploadedMaxSizes;
    private $validator;

    public function __construct(array $uploadedMaxSizes, ValidatorInterface $validator)
    {
        $this->uploadedMaxSizes = $uploadedMaxSizes;
        $this->validator = $validator;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UploadedFile) {
            throw new UnexpectedTypeException($constraint, UploadedFile::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'Symfony\Component\HttpFoundation\File\UploadedFile');
        }

        if (!array_key_exists($constraint->relatedObjectName, $this->uploadedMaxSizes)) {
            throw new InvalidOptionsException('The value of the "relatedObjectName" option does not match an existing related object name.', ['relatedObjectName']);
        }

        if (!$this->uploadedMaxSizes[$constraint->relatedObjectName]) {
            return;
        }

        $className = str_contains($value->getMimeType(), 'image/') ? Assert\Image::class : Assert\File::class;

        /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $errors */
        $errors = $this->validator->validate($value, new $className(['maxSize' => $this->uploadedMaxSizes[$constraint->relatedObjectName]]));
//         dd($errors);

        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
        foreach ($errors as $error) {
            $this->context->buildViolation($error->getMessage(), $error->getParameters())
                 ->setInvalidValue($error->getInvalidValue())
                 ->atPath($constraint->errorPath ?? $error->getPropertyPath())
                 ->addViolation();
        }
    }
}
