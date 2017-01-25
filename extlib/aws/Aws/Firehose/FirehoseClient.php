<?php
namespace Aws\Firehose;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Kinesis Firehose** service.
 *
 * @method \Aws\Result createDeliveryStream(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createDeliveryStreamAsync(array $args = [])
 * @method \Aws\Result deleteDeliveryStream(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteDeliveryStreamAsync(array $args = [])
 * @method \Aws\Result describeDeliveryStream(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeDeliveryStreamAsync(array $args = [])
 * @method \Aws\Result listDeliveryStreams(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listDeliveryStreamsAsync(array $args = [])
 * @method \Aws\Result putRecord(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putRecordAsync(array $args = [])
 * @method \Aws\Result putRecordBatch(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putRecordBatchAsync(array $args = [])
 * @method \Aws\Result updateDestination(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateDestinationAsync(array $args = [])
 */
class FirehoseClient extends AwsClient {}
