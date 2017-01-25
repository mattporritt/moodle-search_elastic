<?php
namespace Aws\S3;

use Aws\CacheInterface;
use Aws\CommandInterface;
use Aws\LruArrayCache;
use Aws\MultiRegionClient as BaseClient;
use Aws\S3\Exception\PermanentRedirectException;
use GuzzleHttpv6\Promise;

/**
 * **Amazon Simple Storage Service** multi-region client.
 *
 * @method \Aws\Result abortMultipartUpload(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise abortMultipartUploadAsync(array $args = [])
 * @method \Aws\Result completeMultipartUpload(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise completeMultipartUploadAsync(array $args = [])
 * @method \Aws\Result copyObject(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise copyObjectAsync(array $args = [])
 * @method \Aws\Result createBucket(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createBucketAsync(array $args = [])
 * @method \Aws\Result createMultipartUpload(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createMultipartUploadAsync(array $args = [])
 * @method \Aws\Result deleteBucket(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBucketAsync(array $args = [])
 * @method \Aws\Result deleteBucketAnalyticsConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBucketAnalyticsConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteBucketCors(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBucketCorsAsync(array $args = [])
 * @method \Aws\Result deleteBucketInventoryConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBucketInventoryConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteBucketLifecycle(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBucketLifecycleAsync(array $args = [])
 * @method \Aws\Result deleteBucketMetricsConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBucketMetricsConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteBucketPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBucketPolicyAsync(array $args = [])
 * @method \Aws\Result deleteBucketReplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBucketReplicationAsync(array $args = [])
 * @method \Aws\Result deleteBucketTagging(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBucketTaggingAsync(array $args = [])
 * @method \Aws\Result deleteBucketWebsite(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBucketWebsiteAsync(array $args = [])
 * @method \Aws\Result deleteObject(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteObjectAsync(array $args = [])
 * @method \Aws\Result deleteObjectTagging(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteObjectTaggingAsync(array $args = [])
 * @method \Aws\Result deleteObjects(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteObjectsAsync(array $args = [])
 * @method \Aws\Result getBucketAccelerateConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketAccelerateConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketAcl(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketAclAsync(array $args = [])
 * @method \Aws\Result getBucketAnalyticsConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketAnalyticsConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketCors(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketCorsAsync(array $args = [])
 * @method \Aws\Result getBucketInventoryConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketInventoryConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketLifecycle(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketLifecycleAsync(array $args = [])
 * @method \Aws\Result getBucketLifecycleConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketLifecycleConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketLocation(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketLocationAsync(array $args = [])
 * @method \Aws\Result getBucketLogging(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketLoggingAsync(array $args = [])
 * @method \Aws\Result getBucketMetricsConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketMetricsConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketNotification(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketNotificationAsync(array $args = [])
 * @method \Aws\Result getBucketNotificationConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketNotificationConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketPolicyAsync(array $args = [])
 * @method \Aws\Result getBucketReplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketReplicationAsync(array $args = [])
 * @method \Aws\Result getBucketRequestPayment(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketRequestPaymentAsync(array $args = [])
 * @method \Aws\Result getBucketTagging(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketTaggingAsync(array $args = [])
 * @method \Aws\Result getBucketVersioning(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketVersioningAsync(array $args = [])
 * @method \Aws\Result getBucketWebsite(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBucketWebsiteAsync(array $args = [])
 * @method \Aws\Result getObject(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getObjectAsync(array $args = [])
 * @method \Aws\Result getObjectAcl(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getObjectAclAsync(array $args = [])
 * @method \Aws\Result getObjectTagging(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getObjectTaggingAsync(array $args = [])
 * @method \Aws\Result getObjectTorrent(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getObjectTorrentAsync(array $args = [])
 * @method \Aws\Result headBucket(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise headBucketAsync(array $args = [])
 * @method \Aws\Result headObject(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise headObjectAsync(array $args = [])
 * @method \Aws\Result listBucketAnalyticsConfigurations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listBucketAnalyticsConfigurationsAsync(array $args = [])
 * @method \Aws\Result listBucketInventoryConfigurations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listBucketInventoryConfigurationsAsync(array $args = [])
 * @method \Aws\Result listBucketMetricsConfigurations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listBucketMetricsConfigurationsAsync(array $args = [])
 * @method \Aws\Result listBuckets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listBucketsAsync(array $args = [])
 * @method \Aws\Result listMultipartUploads(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listMultipartUploadsAsync(array $args = [])
 * @method \Aws\Result listObjectVersions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listObjectVersionsAsync(array $args = [])
 * @method \Aws\Result listObjects(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listObjectsAsync(array $args = [])
 * @method \Aws\Result listObjectsV2(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listObjectsV2Async(array $args = [])
 * @method \Aws\Result listParts(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listPartsAsync(array $args = [])
 * @method \Aws\Result putBucketAccelerateConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketAccelerateConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketAcl(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketAclAsync(array $args = [])
 * @method \Aws\Result putBucketAnalyticsConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketAnalyticsConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketCors(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketCorsAsync(array $args = [])
 * @method \Aws\Result putBucketInventoryConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketInventoryConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketLifecycle(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketLifecycleAsync(array $args = [])
 * @method \Aws\Result putBucketLifecycleConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketLifecycleConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketLogging(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketLoggingAsync(array $args = [])
 * @method \Aws\Result putBucketMetricsConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketMetricsConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketNotification(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketNotificationAsync(array $args = [])
 * @method \Aws\Result putBucketNotificationConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketNotificationConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketPolicyAsync(array $args = [])
 * @method \Aws\Result putBucketReplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketReplicationAsync(array $args = [])
 * @method \Aws\Result putBucketRequestPayment(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketRequestPaymentAsync(array $args = [])
 * @method \Aws\Result putBucketTagging(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketTaggingAsync(array $args = [])
 * @method \Aws\Result putBucketVersioning(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketVersioningAsync(array $args = [])
 * @method \Aws\Result putBucketWebsite(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putBucketWebsiteAsync(array $args = [])
 * @method \Aws\Result putObject(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putObjectAsync(array $args = [])
 * @method \Aws\Result putObjectAcl(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putObjectAclAsync(array $args = [])
 * @method \Aws\Result putObjectTagging(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putObjectTaggingAsync(array $args = [])
 * @method \Aws\Result restoreObject(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise restoreObjectAsync(array $args = [])
 * @method \Aws\Result uploadPart(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise uploadPartAsync(array $args = [])
 * @method \Aws\Result uploadPartCopy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise uploadPartCopyAsync(array $args = [])
 */
class S3MultiRegionClient extends BaseClient implements S3ClientInterface
{
    use S3ClientTrait {
        determineBucketRegionAsync as private lookupBucketRegion;
    }

    /** @var CacheInterface */
    private $cache;

    public static function getArguments()
    {
        $args = parent::getArguments();
        $args['region']['default'] = 'us-east-1';

        return $args + [
            'bucket_region_cache' => [
                'type' => 'config',
                'valid' => [CacheInterface::class],
                'doc' => 'Cache of regions in which given buckets are located.',
                'default' => function () { return new LruArrayCache; },
            ],
        ];
    }

    public function __construct(array $args)
    {
        parent::__construct($args);
        $this->cache = $this->getConfig('bucket_region_cache');
    }

    public function executeAsync(CommandInterface $c)
    {
        return Promise\coroutine(function () use ($c) {
            if ($region = $this->cache->get($this->getCacheKey($c['Bucket']))) {
                $c = $this->getRegionalizedCommand($c, $region);
            }

            try {
                yield parent::executeAsync($c);
            } catch (PermanentRedirectException $e) {
                if (empty($c['Bucket'])) {
                    throw $e;
                }
                $region = (yield $this->lookupBucketRegion($c['Bucket']));
                $this->cache->set($this->getCacheKey($c['Bucket']), $region);
                $c = $this->getRegionalizedCommand($c, $region);
                yield parent::executeAsync($c);
            }
        });
    }

    public function createPresignedRequest(CommandInterface $command, $expires)
    {
        if (empty($command['Bucket'])) {
            throw new \InvalidArgumentException('The S3\\MultiRegionClient'
                . ' cannot create presigned requests for commands without a'
                . ' specified bucket.');
        }

        /** @var S3ClientInterface $client */
        $client = $this->getClientFromPool(
            $this->determineBucketRegion($command['Bucket'])
        );
        return $client->createPresignedRequest(
            $client->getCommand($command->getName(), $command->toArray()),
            $expires
        );
    }

    public function getObjectUrl($bucket, $key)
    {
        /** @var S3Client $regionalClient */
        $regionalClient = $this->getClientFromPool(
            $this->determineBucketRegion($bucket)
        );

        return $regionalClient->getObjectUrl($bucket, $key);
    }

    public function determineBucketRegionAsync($bucketName)
    {
        if ($cached = $this->cache->get($this->getCacheKey($bucketName))) {
            return Promise\promise_for($cached);
        }

        return $this->lookupBucketRegion($bucketName)
            ->then(function ($region) use ($bucketName) {
                $this->cache->set($this->getCacheKey($bucketName), $region);

                return $region;
            });
    }

    private function getRegionalizedCommand(CommandInterface $command, $region)
    {
        return $this->getClientFromPool($region)
            ->getCommand($command->getName(), $command->toArray());
    }

    private function getCacheKey($bucketName)
    {
        return "aws:s3:{$bucketName}:location";
    }
}
