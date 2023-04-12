<?php
namespace Oka\AttachmentManagerBundle\Tests\Validator;

use Oka\AttachmentManagerBundle\Validator\IsRelatedObjectName;
use Oka\AttachmentManagerBundle\Validator\IsRelatedObjectNameValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class IsRelatedObjectNameValidatorTest extends ConstraintValidatorTestCase
{
    public function provideInvalidConstraints(): iterable
    {
        yield [new IsRelatedObjectName()];
    }
    
    /**
     * @covers
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new IsRelatedObjectName());
        
        $this->assertNoViolation();
    }
    
    /**
     * @covers
     * @dataProvider provideInvalidConstraints
     */
    public function testTrueIsInvalid(IsRelatedObjectName $constraint)
    {
        $this->validator->validate('...', $constraint);
        
        $this->buildViolation('The value you selected is not a valid choice.')
             ->setParameter('{{ string }}', '...')
             ->atPath('property.path[relatedObject][name]')
             ->setInvalidValue('...')
             ->assertRaised();             
    }
    
    protected function createValidator()
    {
        return new IsRelatedObjectNameValidator(['acme']);
    }
}
