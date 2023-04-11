<?php

namespace Oka\AttachmentManagerBundle\Tests\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Oka\AttachmentManagerBundle\Tests\Document\Acme;
use Oka\AttachmentManagerBundle\Tests\Document\Attachment;

/**
 * @author Cedrick Oka Baidai <cedric.baidai@veone.net>
 */
class AttachmentDocumentControllerTest extends WebTestCase
{
    public static function setUpBeforeClass(): void
    {
        static::bootKernel();
        
        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = static::$container->get('doctrine_mongodb.odm.document_manager');
        $dm->createQueryBuilder(Attachment::class)->remove()->getQuery()->execute();
        $dm->createQueryBuilder(Acme::class)->remove()->getQuery()->execute();
        
        static::ensureKernelShutdown();
    }
    
    /**
     * @covers
     */
    public function testThatWeCanCreateAttachment()
    {
        $acme = new Acme();
        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = static::$container->get('doctrine_mongodb.odm.document_manager');
        $dm->persist($acme);
        $dm->flush();

        $fs = new Filesystem();
        $targetFile = sprintf('%s/../assets/logo.test.png', __DIR__);
        $fs->copy(sprintf('%s/../assets/logo.png', __DIR__), $targetFile);

        $this->client->request(
            'POST',
            '/v1/rest/attachments',
            [
                'relatedObject' => [
                    'name' => 'acme_mongodb',
                    'identifier' => $acme->getId(),
                ],
            ],
            [
                'file' => new UploadedFile($targetFile, 'logo.png', 'image/png'),
            ],
            [
                'CONTENT_TYPE' => 'multipart/form-data; boundary=---------------------------15989724838008403852242650740',
            ]
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('acme_mongodb', $content['volumeName']);
        $this->assertEquals([], $content['metadata']);
        $this->assertArrayHasKey('filename', $content);
        $this->assertArrayHasKey('lastModified', $content);
        $this->assertArrayHasKey('publicUrl', $content);

        return $content;
    }
}
