<?php

namespace Oka\AttachmentManagerBundle\Tests\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Oka\AttachmentManagerBundle\Test\Entity\Acme;
use Oka\AttachmentManagerBundle\Test\Entity\Attachment;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Cedrick Oka Baidai <cedric.baidai@veone.net>
 */
class AttachmentEntityControllerTest extends AbstractWebTestCase
{
    public static function setUpBeforeClass(): void
    {
        static::bootKernel();

        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = static::$container->get('doctrine.orm.entity_manager');
        $metaData = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($em);
        $schemaTool->updateSchema($metaData);

        $em->createQueryBuilder()->delete(Attachment::class)->getQuery()->execute();
        $em->createQueryBuilder()->delete(Acme::class)->getQuery()->execute();
    }

    /**
     * @covers
     */
    public function testThatWeCanCreateAttachment()
    {
        $acme = new Acme();
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = static::$container->get('doctrine.orm.entity_manager');
        $em->persist($acme);
        $em->flush();

        $fs = new Filesystem();
        $targetFile = sprintf('%s/../assets/centralbill.test.png', __DIR__);
        $fs->copy(sprintf('%s/../assets/centralbill.png', __DIR__), $targetFile);

        $this->client->request(
            'POST',
            '/v1/rest/attachments',
            [
                'relatedObject' => [
                    'name' => 'acme_orm',
                    'identifier' => $acme->getId(),
                ],
            ],
            ['file' => new UploadedFile($targetFile, 'centralbill.png', 'image/png')],
            ['CONTENT_TYPE' => 'multipart/form-data; boundary=---------------------------15989724838008403852242650740']
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('file', $content['volumeName']);
        $this->assertEquals('image/png', $content['metadata']['mime-type']);
        $this->assertArrayHasKey('filename', $content);
        $this->assertArrayHasKey('lastModified', $content);
        $this->assertArrayHasKey('publicUrl', $content);
        $content['relatedObject'] = $acme;

        return $content;
    }

    /**
     * @covers
     *
     * @depends testThatWeCanCreateAttachment
     */
    public function testThatWeCanReadAttachment(array $depends)
    {
        $this->client->request('GET', sprintf('/v1/rest/attachments/%s/acme_orm', $depends['id']));
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('file', $content['volumeName']);
        $this->assertEquals('image/png', $content['metadata']['mime-type']);
        $this->assertArrayHasKey('lastModified', $content);
        $this->assertArrayHasKey('publicUrl', $content);

        return $depends;
    }

    /**
     * @covers
     *
     * @depends testThatWeCanReadAttachment
     */
    public function testThatWeCanUpdateAttachment(array $depends)
    {
        $fs = new Filesystem();
        $targetFile = sprintf('%s/../assets/centralbill.test.png', __DIR__);
        $fs->copy(sprintf('%s/../assets/centralbill.png', __DIR__), $targetFile);

        $this->client->request(
            'POST',
            sprintf('/v1/rest/attachments/%s/acme_orm', $depends['id']),
            [],
            ['file' => new UploadedFile($targetFile, 'centralbill.png', 'image/png')],
            ['CONTENT_TYPE' => 'multipart/form-data; boundary=---------------------------15989724838008403852242650740']
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('file', $content['volumeName']);
        $this->assertEquals('image/png', $content['metadata']['mime-type']);
        $this->assertArrayHasKey('lastModified', $content);
        $this->assertArrayHasKey('publicUrl', $content);

        return $depends;
    }
    
    /**
     * @covers
     *
     * @depends testThatWeCanUpdateAttachment
     */
    public function testThatWeCanAddAttachment(array $depends)
    {
        $fs = new Filesystem();
        $targetFile = sprintf('%s/../assets/centralbill.test.png', __DIR__);
        $fs->copy(sprintf('%s/../assets/centralbill.png', __DIR__), $targetFile);

        $this->client->request(
            'POST',
            '/v1/rest/attachments',
            [
                'relatedObject' => [
                    'name' => 'acme_orm',
                    'identifier' => $depends['relatedObject']->getId(),
                ],
            ],
            ['file' => new UploadedFile($targetFile, 'centralbill.png', 'image/png')],
            ['CONTENT_TYPE' => 'multipart/form-data; boundary=---------------------------15989724838008403852242650740']
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals('[file]', $content['violations'][0]['propertyPath']);
        $this->assertCount(1, $content['violations']);
        $this->assertArrayHasKey('type', $content);
        $this->assertArrayHasKey('title', $content);
        $this->assertArrayHasKey('detail', $content);

        return $depends;
    }

    /**
     * @covers
     *
     * @depends testThatWeCanAddAttachment
     */
    public function testThatWeCanDeleteAttachment(array $depends)
    {
        $this->client->request('DELETE', sprintf('/v1/rest/attachments/%s/acme_orm', $depends['id']));

        $this->assertResponseStatusCodeSame(204);
    }
}
