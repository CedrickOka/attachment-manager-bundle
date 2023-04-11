<?php
namespace Oka\AttachmentManagerBundle\Volume;

use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class FileVolumeHandler implements VolumeHandlerInterface
{
    public function exists(Volume $volume): bool
    {
        return file_exists($volume->getDsn());
    }
    
    public function create(Volume $volume): Volume
    {
        mkdir($volume->getDsn(), 0755, true);
        
        return $volume;
    }
    
    public function delete(Volume $volume, bool $recursive = false): void
    {
        if (true === $recursive) {
            $files = scandir($volume->getDsn());
            
            foreach ($files as $file) {
                if ('.' === $file || '..' === $file) {
                    continue;
                }
                
                $path = sprintf('%s/%s', $volume->getDsn(), $file);
                is_dir($path) ? delete($path, $recursive) : unlink($path);
            }
        }

        rmdir($volume->getDsn());
    }
    
    public function putFile(Volume $volume, AttachmentInterface $attachment, UploadedFile $uploadedFile): void
    {
        $uploadedFile->move($volume->getDsn(), $attachment->getFilename());
    }
    
    public function getFileInfo(Volume $volume, AttachmentInterface $attachment): FileInfo
    {
        return new FileInfo(
            $attachment->getFilename(), 
            $volume->getDsn(), 
            $this->getAttachmentRealPath($volume, $attachment), 
            $this->getFilePublicUrl($volume, $attachment)
        );
    }
    
    public function deleteFile(Volume $volume, AttachmentInterface $attachment): void
    {
        unlink($this->getAttachmentRealPath($volume, $attachment));
    }

    public function getFilePublicUrl(Volume $volume, AttachmentInterface $attachment): string
    {
        return sprintf('%s/%s', $volume->getPublicUrl() ?? '', $attachment->getFilename());
    }
    
    protected function getAttachmentRealPath(Volume $volume, AttachmentInterface $attachment): string
    {
        return sprintf('%s/%s', $volume->getDsn(), $attachment->getFilename());
    }
}
