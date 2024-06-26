<?php

namespace Oka\AttachmentManagerBundle\Volume;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Symfony\Component\HttpFoundation\File\File;

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

    public function putFile(Volume $volume, AttachmentInterface $attachment, File $file): void
    {
        $bucket = $this->getBucketName($volume->getDsn());
        $result = $this->s3Client->createMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $attachment->getFilename(),
            'ContentType' => $file->getMimeType(),
            'Metadata' => $attachment->getMetadata(),
        ]);

        $stream = $file->openFile();
        $uploadId = $result['UploadId'];
        $uploadPartNumber = 0;
        $uploadParts = [];

        try {
            while (false === $stream->eof()) {
                ++$uploadPartNumber;
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

        unlink($file->getRealPath());
    }
    
    public function renameFile(Volume $volume, AttachmentInterface $from, AttachmentInterface $to): void
    {
        rename($this->getAttachmentRealPath($volume, $from), $this->getAttachmentRealPath($volume, $to), stream_context_create(['s3' => ['MetadataDirective' => 'COPY']]));
    }

    //     public function createFileUploadForm(Volume $volume, AttachmentInterface $attachment, string $expiresAt = null): FileUploadForm
    //     {
    //         $belongsToPolicyCondition = [];
    //         $formInputs = [
    //             'Content-Type' => $attachment->getMimeType(),
    //             'key' => $attachment->getFilename(),
    //             'acl' => 'private',
    //             'X-Amz-Meta-Document-Type' => $attachment->getType(),
    //         ];

    //         foreach ($attachment->getMetadata() as $name => $value) {
    //             $formInputs[sprintf('X-Amz-Meta-%s', $name)] = $value;

    //             if ('belongs-to' === strtolower($name)) {
    //                 $belongsToPolicyCondition = ['X-Amz-Meta-Belongs-To' => $value];
    //             }
    //         }

    //         $postObject = new \Aws\S3\PostObjectV4(
    //             $this->objectStorage,
    //             $bucket,
    //             $formInputs,
    //             [
    //                 ['eq', '$key', $attachment->getFilename()],
    //                 ['eq', '$acl' => 'private'],
    //                 $belongsToPolicyCondition,
    //                 ['X-Amz-Meta-Document-Type' => $attachment->getType()],
    //             ],
    //             $expiresAt ?? date('c', time() + 3600)
    //             );

    //         return [
    //             'attributes' => $postObject->getFormAttributes(),
    //             'inputs' => $postObject->getFormInputs(),
    //         ];
    //     }

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
            '+1440 minutes'
        );

        return $presignedRequest->getUri();
    }

    private function getBucketName(string $dsn): string
    {
        return substr($dsn, strpos($dsn, 's3://') + 5);
    }
}
