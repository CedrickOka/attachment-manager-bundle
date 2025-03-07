<?php

namespace Oka\AttachmentManagerBundle\Validator;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class UploadedFileValidator extends ConstraintValidator
{
    public function __construct(
        private ValidatorInterface $validator, 
        private ServiceLocator $attachmentManagerLocator,
        private array $relatedObjectDBDriverMapping, 
        private array $relatedObjectUploadedMaxSizes, 
        private array $relatedObjectUploadedMaxCounts
    ) {
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

        if (!array_key_exists($constraint->relatedObjectName, $this->relatedObjectUploadedMaxSizes)) {
            throw new InvalidOptionsException('The value of the "relatedObjectName" option does not match an existing related object name.', ['relatedObjectName']);
        }

        if ($this->relatedObjectUploadedMaxSizes[$constraint->relatedObjectName]) {
            $className = str_contains($value->getMimeType(), 'image/') ? Assert\Image::class : Assert\File::class;
            /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $errors */
            $errors = $this->validator->validate($value, new $className(['maxSize' => $this->relatedObjectUploadedMaxSizes[$constraint->relatedObjectName]]));

            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $this->context->buildViolation($error->getMessage(), $error->getParameters())
                            ->setInvalidValue($error->getInvalidValue())
                            ->atPath($constraint->errorPath ?? $error->getPropertyPath())
                            ->addViolation();
            }
        }

        if (null !== $constraint->relatedObjectIdentifier && $this->relatedObjectUploadedMaxCounts[$constraint->relatedObjectName] > 0) {
            /** @var \Oka\AttachmentManagerBundle\Model\AttachmentManagerInterface $attachmentManager */
            $attachmentManager = $this->attachmentManagerLocator->get($this->relatedObjectDBDriverMapping[$constraint->relatedObjectName]);
            $relatedObject = $attachmentManager->getObjectManager()->find($attachmentManager->getRelatedObjets()->get($constraint->relatedObjectName)['class'], $constraint->relatedObjectIdentifier);
            /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $errors */
            $errors = $this->validator->validate(1 + $relatedObject->getAttachments()->count(), new Range(['min' => 0, 'max' => $this->relatedObjectUploadedMaxCounts[$constraint->relatedObjectName]]));

            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $this->context->buildViolation($error->getMessage(), $error->getParameters())
                            ->setInvalidValue($error->getInvalidValue())
                            ->atPath($constraint->errorPath ?? $error->getPropertyPath())
                            ->addViolation();
            }
        }
    }
}
