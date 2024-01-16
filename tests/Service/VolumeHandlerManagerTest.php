<?php
namespace Oka\AttachmentManagerBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Oka\AttachmentManagerBundle\Service\VolumeHandlerManager;
use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Oka\AttachmentManagerBundle\Tests\Document\Attachment;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Uid\Uuid;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class VolumeHandlerManagerTest extends KernelTestCase
{
    /**
     * @var VolumeHandlerManager
     */
    protected $manager;
    
    protected function setUp(): void
    {
        static::bootKernel();
        
        $this->manager = static::getContainer()->get(VolumeHandlerManager::class);
    }
    
    /**
     * @covers
     */
    public function testThatWeCanCheckIfVolumeExist()
    {
        $this->assertFalse($this->manager->exists('test'));
    }
    
    /**
     * @covers
     *
     * @depends testThatWeCanCheckIfVolumeExist
     */
    public function testThatWeCanCreateVolume()
    {
        $this->manager->create('test');
        
        $this->assertTrue($this->manager->exists('test'));
    }
    
    /**
     * @covers
     *
     * @depends testThatWeCanCreateVolume
     */
    public function testThatWeCanPutFileInVolume()
    {
        $fs = new Filesystem();
        $targetFile = sprintf('%s/../assets/logo.test.png', __DIR__);
        $fs->copy(sprintf('%s/../assets/logo.png', __DIR__), $targetFile);
        $file = new UploadedFile($targetFile, 'logo.png', 'image/png', null, true);
        
        /** @var AttachmentInterface $attachment */
        $attachment = new Attachment();
        $attachment->setVolumeName('test');
        $attachment->setLastModified();
        
        $mimeTypes = new MimeTypes();
        $extensions = $mimeTypes->getExtensions($file->getMimeType());
        $attachment->setFilename(sprintf('s%s', Uuid::v4()->__toString(), isset($extensions[0]) ? '.'.$extensions[0] : ''));
        
        $this->manager->putFile($attachment, $file);
        $fileInfo = $this->manager->getFileInfo($attachment);
        
        $this->assertEquals($attachment->getFilename(), $fileInfo->getName());
        $this->assertStringContainsString($attachment->getFilename(), $fileInfo->getPublicUrl());

        return $attachment;
    }
    
    /**
     * @covers
     *
     * @depends testThatWeCanPutFileInVolume
     */
    public function testThatWeCanDeleteAttachmentInVolume(AttachmentInterface $attachment)
    {
        $this->manager->deleteFile($attachment);
        $fileInfo = $this->manager->getFileInfo($attachment);
        
        $this->assertFalse(file_exists($fileInfo->getRealPath()));
    }
    
    /**
     * @covers
     *
     * @depends testThatWeCanDeleteAttachmentInVolume
     */
    public function testThatWeCanDeleteVolume()
    {
        $this->manager->delete('test');
        
        $this->assertFalse($this->manager->exists('test'));
    }
}
