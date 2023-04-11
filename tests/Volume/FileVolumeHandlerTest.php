<?php
namespace Oka\AttachmentManagerBundle\Tests\Volume;

use PHPUnit\Framework\TestCase;
use Oka\AttachmentManagerBundle\Volume\FileVolumeHandler;
use Oka\AttachmentManagerBundle\Volume\Volume;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class FileVolumeHandlerTest extends TestCase
{
    /**
     * @var \Oka\AttachmentManagerBundle\Volume\VolumeHandlerInterface
     */
    protected $handler;
    
    protected function setUp(): void
    {
        $this->handler = new FileVolumeHandler();
    }
    
    /**
     * @covers
     */
    public function testThatWeCanCheckIfVolumeExist()
    {
        $this->assertFalse($this->handler->exists(new Volume('file', 'file:///tmp/acme')));
    }

    /**
     * @covers
     * @depends testThatWeCanCheckIfVolumeExist
     */
    public function testThatWeCanCreateVolume()
    {
        $volume = new Volume('file', 'file:///tmp/acme');
        $this->handler->create($volume);
        
        $this->assertTrue($this->handler->exists($volume));
    }
    
    /**
     * @covers
     * @depends testThatWeCanCreateVolume
     */
    public function testThatWeCanDeleteVolume()
    {
        $volume = new Volume('file', 'file:///tmp/acme');
        $this->handler->delete($volume);
        
        $this->assertFalse($this->handler->exists($volume));
    }
}
