<?php
namespace Aws\CloudTrail;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS CloudTrail** service.
 *
 * @method \Aws\Result addTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise addTagsAsync(array $args = [])
 * @method \Aws\Result createTrail(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createTrailAsync(array $args = [])
 * @method \Aws\Result deleteTrail(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteTrailAsync(array $args = [])
 * @method \Aws\Result describeTrails(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeTrailsAsync(array $args = [])
 * @method \Aws\Result getEventSelectors(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getEventSelectorsAsync(array $args = [])
 * @method \Aws\Result getTrailStatus(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getTrailStatusAsync(array $args = [])
 * @method \Aws\Result listPublicKeys(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listPublicKeysAsync(array $args = [])
 * @method \Aws\Result listTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTagsAsync(array $args = [])
 * @method \Aws\Result lookupEvents(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise lookupEventsAsync(array $args = [])
 * @method \Aws\Result putEventSelectors(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putEventSelectorsAsync(array $args = [])
 * @method \Aws\Result removeTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise removeTagsAsync(array $args = [])
 * @method \Aws\Result startLogging(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise startLoggingAsync(array $args = [])
 * @method \Aws\Result stopLogging(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise stopLoggingAsync(array $args = [])
 * @method \Aws\Result updateTrail(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateTrailAsync(array $args = [])
 */
class CloudTrailClient extends AwsClient {}
