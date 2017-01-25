<?php
namespace Aws\Kinesis;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Kinesis** service.
 *
 * @method \Aws\Result addTagsToStream(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise addTagsToStreamAsync(array $args = [])
 * @method \Aws\Result createStream(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createStreamAsync(array $args = [])
 * @method \Aws\Result decreaseStreamRetentionPeriod(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise decreaseStreamRetentionPeriodAsync(array $args = [])
 * @method \Aws\Result deleteStream(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteStreamAsync(array $args = [])
 * @method \Aws\Result describeLimits(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeLimitsAsync(array $args = [])
 * @method \Aws\Result describeStream(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeStreamAsync(array $args = [])
 * @method \Aws\Result disableEnhancedMonitoring(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise disableEnhancedMonitoringAsync(array $args = [])
 * @method \Aws\Result enableEnhancedMonitoring(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise enableEnhancedMonitoringAsync(array $args = [])
 * @method \Aws\Result getRecords(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getRecordsAsync(array $args = [])
 * @method \Aws\Result getShardIterator(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getShardIteratorAsync(array $args = [])
 * @method \Aws\Result increaseStreamRetentionPeriod(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise increaseStreamRetentionPeriodAsync(array $args = [])
 * @method \Aws\Result listStreams(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listStreamsAsync(array $args = [])
 * @method \Aws\Result listTagsForStream(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTagsForStreamAsync(array $args = [])
 * @method \Aws\Result mergeShards(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise mergeShardsAsync(array $args = [])
 * @method \Aws\Result putRecord(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putRecordAsync(array $args = [])
 * @method \Aws\Result putRecords(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putRecordsAsync(array $args = [])
 * @method \Aws\Result removeTagsFromStream(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise removeTagsFromStreamAsync(array $args = [])
 * @method \Aws\Result splitShard(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise splitShardAsync(array $args = [])
 * @method \Aws\Result updateShardCount(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateShardCountAsync(array $args = [])
 */
class KinesisClient extends AwsClient {}
