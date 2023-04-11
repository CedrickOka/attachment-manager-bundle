<?php
namespace Oka\AttachmentManagerBundle\Tests\Volume;

use PHPUnit\Framework\TestCase;
use Oka\AttachmentManagerBundle\Volume\FileVolumeHandler;
use Oka\AttachmentManagerBundle\Volume\Volume;
use Oka\AttachmentManagerBundle\Volume\S3VolumeHandlerFactory;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class S3VolumeHandlerTest extends TestCase
{
    /**
     * @var Volume
     */
    protected $volume;
    
    /**
     * @var \Oka\AttachmentManagerBundle\Volume\VolumeHandlerInterface
     */
    protected $handler;
    
    protected function setUp(): void
    {
        $this->volume = new Volume(
            's3', 
            's3://acme', 
            [
                'version' => 'latest',
                'region' => 'africa',
                'use_path_style_endpoint' => true,
                'endpoint' => getenv('OBJECT_STORAGE_URL'),
                'credentials' => [
                    'key' => getenv('OBJECT_STORAGE_ROOT_USER'),
                    'secret' => getenv('OBJECT_STORAGE_ROOT_PASSWORD'),
                ]
            ],
            getenv('OBJECT_STORAGE_PUBLIC_URL')
        );
        
        $factory = new S3VolumeHandlerFactory();
        $this->handler = $factory->create($this->volume->getDsn(), $this->volume->getOptions());
    }
    
    /**
     * @covers
     */
    public function testThatWeCanCheckIfVolumeExist()
    {
        $this->assertFalse($this->handler->exists($this->volume));
    }

    /**
     * @covers
     * @depends testThatWeCanCheckIfVolumeExist
     */
    public function testThatWeCanCreateVolume()
    {
        $this->handler->create($this->volume);
        
        $this->assertTrue($this->handler->exists($this->volume));
    }
    
    /**
     * @covers
     * @depends testThatWeCanCreateVolume
     */
    public function testThatWeCanDeleteVolume()
    {
        $this->handler->delete($this->volume);
        
        $this->assertFalse($this->handler->exists($this->volume));
    }
}
