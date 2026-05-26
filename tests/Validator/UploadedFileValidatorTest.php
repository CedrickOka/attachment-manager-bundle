<?php

namespace Oka\AttachmentManagerBundle\Tests\Validator;

use Oka\AttachmentManagerBundle\Validator\UploadedFile;
use Oka\AttachmentManagerBundle\Validator\UploadedFileValidator;
use Symfony\Component\DependencyInjection\Argument\ServiceLocator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\FileValidator;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\MetadataInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class UploadedFileValidatorTest extends ConstraintValidatorTestCase
{
    public function provideInvalidConstraints(): iterable
    {
        yield [new UploadedFile('acme')];
    }

    /**
     * @covers
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new UploadedFile('acme'));

        $this->assertNoViolation();
    }

    /**
     * @covers
     *
     * @dataProvider provideInvalidConstraints
     */
    public function testTrueIsInvalid(UploadedFile $constraint)
    {
        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile(sprintf('%s/../assets/centralbill.png', __DIR__), 'centralbill.png', 'image/png', null, true);
        $this->validator->validate($file, $constraint);

        $this->assertNoViolation();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new UploadedFileValidator(
            new class($this->context) implements ValidatorInterface {
                private $context;

                public function __construct(ExecutionContextInterface $context)
                {
                    $this->context = $context;
                }

                public function validate(mixed $value, Constraint|array|null $constraints = null, string|GroupSequence|array|null $groups = null): ConstraintViolationListInterface
                {
                    $validator = new FileValidator();
                    $validator->initialize($this->context);
                    $validator->validate($value, $constraints, $groups);

                    return $this->context->getViolations();
                }

                public function validateProperty(object $object, string $propertyName, string|GroupSequence|array|null $groups = null): ConstraintViolationListInterface
                {
                }

                public function validatePropertyValue(object|string $objectOrClass, string $propertyName, mixed $value, string|GroupSequence|array|null $groups = null): ConstraintViolationListInterface
                {
                }

                public function startContext(): ContextualValidatorInterface
                {
                }

                public function inContext(ExecutionContextInterface $context): ContextualValidatorInterface
                {
                }

                public function getMetadataFor(mixed $value): MetadataInterface
                {
                }

                public function hasMetadataFor(mixed $value): bool
                {
                }
            },
            new ServiceLocator(function () {}, []),
            [],
            ['acme' => '5ki'],
            ['acme' => 3]
        );
    }
}
