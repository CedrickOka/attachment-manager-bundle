<?php

namespace Oka\AttachmentManagerBundle\Tests\Controller;

use Oka\AttachmentManagerBundle\Tests\Document\Acme;
use Oka\AttachmentManagerBundle\Tests\Document\Attachment;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Cedrick Oka Baidai <cedric.baidai@veone.net>
 */
class AttachmentDocumentControllerTest extends AbstractWebTestCase
{
    public static function setUpBeforeClass(): void
    {
        static::bootKernel();

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = static::$container->get('doctrine_mongodb.odm.document_manager');
        $dm->createQueryBuilder(Attachment::class)->remove()->getQuery()->execute();
        $dm->createQueryBuilder(Acme::class)->remove()->getQuery()->execute();
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
                'metadata' => [
                    'relatedObject' => 'acme_mongodb',
                ],
            ],
            ['file' => new UploadedFile($targetFile, 'logo.png', 'image/png')],
            ['CONTENT_TYPE' => 'multipart/form-data; boundary=---------------------------15989724838008403852242650740']
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('s3', $content['volumeName']);
        $this->assertEquals(['relatedObject' => 'acme_mongodb'], $content['metadata']);
        $this->assertArrayHasKey('filename', $content);
        $this->assertArrayHasKey('lastModified', $content);
        $this->assertArrayHasKey('publicUrl', $content);

        return $content;
    }

    /**
     * @covers
     *
     * @depends testThatWeCanCreateAttachment
     */
    public function testThatWeCanReadAttachment(array $depends)
    {
        $this->client->request('GET', sprintf('/v1/rest/attachments/%s/acme_mongodb', $depends['id']));
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('s3', $content['volumeName']);
        $this->assertEquals(['relatedObject' => 'acme_mongodb'], $content['metadata']);
        $this->assertArrayHasKey('lastModified', $content);
        $this->assertArrayHasKey('publicUrl', $content);

        return $content;
    }

    /**
     * @covers
     *
     * @depends testThatWeCanReadAttachment
     */
    public function testThatWeCanUpdateAttachment(array $depends)
    {
        $fs = new Filesystem();
        $targetFile = sprintf('%s/../assets/logo.test.png', __DIR__);
        $fs->copy(sprintf('%s/../assets/logo.png', __DIR__), $targetFile);

        $this->client->request(
            'POST',
            sprintf('/v1/rest/attachments/%s/acme_mongodb', $depends['id']),
            [],
            ['file' => new UploadedFile($targetFile, 'logo.png', 'image/png')],
            ['CONTENT_TYPE' => 'multipart/form-data; boundary=---------------------------15989724838008403852242650740']
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('s3', $content['volumeName']);
        $this->assertEquals(['relatedObject' => 'acme_mongodb'], $content['metadata']);
        $this->assertArrayHasKey('lastModified', $content);
        $this->assertArrayHasKey('publicUrl', $content);

        return $content;
    }

    /**
     * @covers
     *
     * @depends testThatWeCanUpdateAttachment
     */
    public function testThatWeCanDeleteAttachment(array $depends)
    {
        $this->client->request('DELETE', sprintf('/v1/rest/attachments/%s/acme_mongodb', $depends['id']));

        $this->assertResponseStatusCodeSame(204);
    }
}
