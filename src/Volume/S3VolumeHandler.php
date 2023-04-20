<?php

namespace Oka\AttachmentManagerBundle\Volume;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class S3VolumeHandler extends FileVolumeHandler
{
    private $s3Client;
    private $cachedPublicS3Client = [];

    public function __construct(S3ClientInterface $s3Client)
    {
        $s3Client->registerStreamWrapper();

        $this->s3Client = $s3Client;
    }

    public function exists(Volume $volume): bool
    {
        return $this->s3Client->doesBucketExistV2($this->getBucketName($volume->getDsn()));
    }

    public function delete(Volume $volume, bool $recursive = false): void
    {
        $bucket = $this->getBucketName($volume->getDsn());

        if (true === $this->s3Client->doesBucketExistV2($bucket)) {
            if (true === $recursive) {
                $this->s3Client->deleteMatchingObjects($bucket, '', '#.*#');
            }

            $this->s3Client->deleteBucket(['Bucket' => $bucket]);
        }
    }

    public function putFile(Volume $volume, AttachmentInterface $attachment, UploadedFile $uploadedFile): void
    {
        $bucket = $this->getBucketName($volume->getDsn());
        $result = $this->s3Client->createMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $attachment->getFilename(),
            'ContentType' => $uploadedFile->getMimeType(),
        ]);

        $stream = $uploadedFile->openFile();
        $uploadId = $result['UploadId'];
        $uploadPartNumber = 0;
        $uploadParts = [];

        try {
            ++$uploadPartNumber;

            while (false === $stream->eof()) {
                $result = $this->s3Client->uploadPart([
                    'Bucket' => $bucket,
                    'Key' => $attachment->getFilename(),
                    'UploadId' => $uploadId,
                    'PartNumber' => $uploadPartNumber,
                    'Body' => $stream->fread(5242880),
                ]);

                $uploadParts['Parts'][$uploadPartNumber] = [
                    'PartNumber' => $uploadPartNumber,
                    'ETag' => $result['ETag'],
                ];
            }
        } catch (S3Exception $e) {
            $result = $this->s3Client->abortMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $attachment->getFilename(),
                'UploadId' => $uploadId,
            ]);

            throw $e;
        }

        // Complete the multipart upload.
        $this->s3Client->completeMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $attachment->getFilename(),
            'UploadId' => $uploadId,
            'MultipartUpload' => $uploadParts,
        ]);

        unlink($uploadedFile->getRealPath());
    }

    public function getFilePublicUrl(Volume $volume, AttachmentInterface $attachment): string
    {
        if (!isset($this->cachedPublicS3Client[$volume->getPublicUrl()])) {
            $this->cachedPublicS3Client[$volume->getPublicUrl()] = new S3Client([
                'version' => 'latest',
                'region' => 'africa',
                'use_path_style_endpoint' => true,
                ...$volume->getOptions(),
                'endpoint' => $volume->getPublicUrl(),
            ]);
        }

        /** @var S3ClientInterface $publicS3Client */
        $publicS3Client = $this->cachedPublicS3Client[$volume->getPublicUrl()];
        $presignedRequest = $publicS3Client->createPresignedRequest(
            $publicS3Client->getCommand(
                'GetObject',
                [
                    'Bucket' => $this->getBucketName($volume->getDsn()),
                    'Key' => $attachment->getFilename(),
                ]
            ),
            '+60 minutes'
        );

        return $presignedRequest->getUri();
    }

    private function getBucketName(string $dsn): string
    {
        return substr($dsn, strpos($dsn, 's3://') + 5);
    }
}
