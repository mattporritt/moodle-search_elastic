<?php
namespace Aws\IotDataPlane;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Data Plane** service.
 *
 * @method \Aws\Result deleteThingShadow(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteThingShadowAsync(array $args = [])
 * @method \Aws\Result getThingShadow(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getThingShadowAsync(array $args = [])
 * @method \Aws\Result publish(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise publishAsync(array $args = [])
 * @method \Aws\Result updateThingShadow(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateThingShadowAsync(array $args = [])
 */
class IotDataPlaneClient extends AwsClient {}
