<?php

namespace Oka\AttachmentManagerBundle\Tests\Validator;

use Oka\AttachmentManagerBundle\Validator\UploadedFile;
use Oka\AttachmentManagerBundle\Validator\UploadedFileValidator;
use Symfony\Component\Validator\Constraints\FileValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class UploadedFileValidatorTest extends ConstraintValidatorTestCase
{
    public function provideInvalidConstraints(): iterable
    {
        yield [new UploadedFile(['relatedObjectName' => 'acme'])];
    }

    /**
     * @covers
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new UploadedFile(['relatedObjectName' => 'acme']));

        $this->assertNoViolation();
    }

    /**
     * @covers
     *
     * @dataProvider provideInvalidConstraints
     */
    public function testTrueIsInvalid(UploadedFile $constraint)
    {
        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile(sprintf('%s/../assets/logo.png', __DIR__), 'logo.png', 'image/png', null, true);
        $this->validator->validate($file, $constraint);

        $this->assertNoViolation();
    }

    protected function createValidator()
    {
        return new UploadedFileValidator(['acme' => '5ki'], new class($this->context) implements ValidatorInterface {
            private $context;

            public function __construct(ExecutionContextInterface $context)
            {
                $this->context = $context;
            }

            public function validate($value, $constraints = null, $groups = null)
            {
                $validator = new FileValidator();
                $validator->initialize($this->context);
                $validator->validate($value, $constraints, $groups);

                return $this->context->getViolations();
            }

            public function validateProperty(object $object, string $propertyName, $groups = null)
            {
            }

            public function validatePropertyValue($objectOrClass, string $propertyName, $value, $groups = null)
            {
            }

            public function startContext()
            {
            }

            public function inContext(ExecutionContextInterface $context)
            {
            }

            public function getMetadataFor($value)
            {
            }

            public function hasMetadataFor($value)
            {
            }
        });
    }
}
