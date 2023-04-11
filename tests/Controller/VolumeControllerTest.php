<?php

namespace Oka\AttachmentManagerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Cedrick Oka Baidai <cedric.baidai@veone.net>
 */
class VolumeControllerTest extends WebTestCase
{
    /**
     * @covers
     */
    public function testThatWeCanCreateVolume()
    {
        $this->client->request('POST', '/v1/rest/volumes', ['name' => 's3']);
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('s3', $content['name']);
        $this->assertEquals('s3://acme', $content['dsn']);
        $this->assertEquals('http://localhost:9000', $content['publicUrl']);
        $this->assertArrayHasKey('options', $content);
        
        return $content;
    }

    /**
     * @covers
     * @depends testThatWeCanCreateVolume
     */
    public function testThatWeCanDeleteVolume($depends)
    {
        $this->client->request('DELETE', sprintf('/v1/rest/volumes/%s?recursive', $depends['name']));
    
        $this->assertResponseStatusCodeSame(204);
    }
        
    protected function setUp(): void
    {
        static::ensureKernelShutdown();
        $this->client = static::createClient();
    }
}
