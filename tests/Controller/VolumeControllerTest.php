<?php

namespace Oka\AttachmentManagerBundle\Tests\Controller;

/**
 * @author Cedrick Oka Baidai <cedric.baidai@veone.net>
 */
class VolumeControllerTest extends AbstractWebTestCase
{
    /**
     * @covers
     */
    public function testThatWeCanCreateVolume()
    {
        $this->client->request('POST', '/v1/rest/volumes', ['name' => 'folder']);
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('folder', $content['name']);
        $this->assertEquals('file:///tmp/folder', $content['dsn']);
        $this->assertEquals('http://localhost', $content['publicUrl']);
        $this->assertArrayHasKey('options', $content);

        return $content;
    }

    /**
     * @covers
     *
     * @depends testThatWeCanCreateVolume
     */
    public function testThatWeCanDeleteVolume($depends)
    {
        $this->client->request('DELETE', sprintf('/v1/rest/volumes/%s?recursive', $depends['name']));

        $this->assertResponseStatusCodeSame(204);
    }
}
