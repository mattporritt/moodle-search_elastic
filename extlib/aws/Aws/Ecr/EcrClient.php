<?php
namespace Aws\Ecr;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon EC2 Container Registry** service.
 *
 * @method \Aws\Result batchCheckLayerAvailability(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise batchCheckLayerAvailabilityAsync(array $args = [])
 * @method \Aws\Result batchDeleteImage(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise batchDeleteImageAsync(array $args = [])
 * @method \Aws\Result batchGetImage(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise batchGetImageAsync(array $args = [])
 * @method \Aws\Result completeLayerUpload(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise completeLayerUploadAsync(array $args = [])
 * @method \Aws\Result createRepository(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createRepositoryAsync(array $args = [])
 * @method \Aws\Result deleteRepository(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteRepositoryAsync(array $args = [])
 * @method \Aws\Result deleteRepositoryPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteRepositoryPolicyAsync(array $args = [])
 * @method \Aws\Result describeImages(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeImagesAsync(array $args = [])
 * @method \Aws\Result describeRepositories(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeRepositoriesAsync(array $args = [])
 * @method \Aws\Result getAuthorizationToken(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getAuthorizationTokenAsync(array $args = [])
 * @method \Aws\Result getDownloadUrlForLayer(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getDownloadUrlForLayerAsync(array $args = [])
 * @method \Aws\Result getRepositoryPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getRepositoryPolicyAsync(array $args = [])
 * @method \Aws\Result initiateLayerUpload(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise initiateLayerUploadAsync(array $args = [])
 * @method \Aws\Result listImages(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listImagesAsync(array $args = [])
 * @method \Aws\Result putImage(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putImageAsync(array $args = [])
 * @method \Aws\Result setRepositoryPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise setRepositoryPolicyAsync(array $args = [])
 * @method \Aws\Result uploadLayerPart(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise uploadLayerPartAsync(array $args = [])
 */
class EcrClient extends AwsClient {}
