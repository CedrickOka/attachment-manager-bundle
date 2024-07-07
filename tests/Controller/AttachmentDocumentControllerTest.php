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
        $targetFile = sprintf('%s/../assets/centralbill.test.png', __DIR__);
        $fs->copy(sprintf('%s/../assets/centralbill.png', __DIR__), $targetFile);

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
            ['file' => new UploadedFile($targetFile, 'centralbill.png', 'image/png')],
            ['CONTENT_TYPE' => 'multipart/form-data; boundary=---------------------------15989724838008403852242650740']
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('s3', $content['volumeName']);
        $this->assertStringContainsString('.png', $content['filename']);
        $this->assertEquals('acme_mongodb', $content['metadata']['relatedObject']);
        $this->assertEquals('image/png', $content['metadata']['mime-type']);
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
        $this->assertStringContainsString('.png', $content['filename']);
        $this->assertEquals('acme_mongodb', $content['metadata']['relatedObject']);
        $this->assertEquals('image/png', $content['metadata']['mime-type']);
        $this->assertEquals($depends['lastModified'], $content['lastModified']);
        $this->assertEquals($depends['publicUrl'], $content['publicUrl']);

        return $content;
    }

    /**
     * @covers
     *
     * @depends testThatWeCanReadAttachment
     */
    public function testThatWeCanUpdateAttachment(array $depends)
    {
        sleep(3);
        $fs = new Filesystem();
        $targetFile = sprintf('%s/../assets/aynid.test.ico', __DIR__);
        $fs->copy(sprintf('%s/../assets/aynid.ico', __DIR__), $targetFile);

        $this->client->request(
            'POST',
            sprintf('/v1/rest/attachments/%s/acme_mongodb', $depends['id']),
            [],
            ['file' => new UploadedFile($targetFile, 'aynid.ico', 'image/x-icon')],
            ['CONTENT_TYPE' => 'multipart/form-data; boundary=---------------------------15989724838008403852242650740']
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('s3', $content['volumeName']);
        $this->assertStringContainsString('.ico', $content['filename']);
        $this->assertEquals('acme_mongodb', $content['metadata']['relatedObject']);
        $this->assertEquals('image/vnd.microsoft.icon', $content['metadata']['mime-type']);
        $this->assertNotEquals($depends['lastModified'], $content['lastModified']);
        $this->assertNotEquals($depends['publicUrl'], $content['publicUrl']);

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
