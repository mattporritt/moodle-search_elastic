<?php
namespace Aws\SnowBall;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Import/Export Snowball** service.
 * @method \Aws\Result cancelCluster(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise cancelClusterAsync(array $args = [])
 * @method \Aws\Result cancelJob(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise cancelJobAsync(array $args = [])
 * @method \Aws\Result createAddress(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createAddressAsync(array $args = [])
 * @method \Aws\Result createCluster(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createClusterAsync(array $args = [])
 * @method \Aws\Result createJob(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createJobAsync(array $args = [])
 * @method \Aws\Result describeAddress(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeAddressAsync(array $args = [])
 * @method \Aws\Result describeAddresses(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeAddressesAsync(array $args = [])
 * @method \Aws\Result describeCluster(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeClusterAsync(array $args = [])
 * @method \Aws\Result describeJob(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeJobAsync(array $args = [])
 * @method \Aws\Result getJobManifest(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getJobManifestAsync(array $args = [])
 * @method \Aws\Result getJobUnlockCode(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getJobUnlockCodeAsync(array $args = [])
 * @method \Aws\Result getSnowballUsage(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getSnowballUsageAsync(array $args = [])
 * @method \Aws\Result listClusterJobs(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listClusterJobsAsync(array $args = [])
 * @method \Aws\Result listClusters(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listClustersAsync(array $args = [])
 * @method \Aws\Result listJobs(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listJobsAsync(array $args = [])
 * @method \Aws\Result updateCluster(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateClusterAsync(array $args = [])
 * @method \Aws\Result updateJob(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateJobAsync(array $args = [])
 */
class SnowBallClient extends AwsClient {}
