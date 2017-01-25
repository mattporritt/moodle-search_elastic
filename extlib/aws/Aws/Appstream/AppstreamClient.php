<?php
namespace Aws\Appstream;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon AppStream** service.
 * @method \Aws\Result associateFleet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise associateFleetAsync(array $args = [])
 * @method \Aws\Result createFleet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createFleetAsync(array $args = [])
 * @method \Aws\Result createStack(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createStackAsync(array $args = [])
 * @method \Aws\Result createStreamingURL(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createStreamingURLAsync(array $args = [])
 * @method \Aws\Result deleteFleet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteFleetAsync(array $args = [])
 * @method \Aws\Result deleteStack(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteStackAsync(array $args = [])
 * @method \Aws\Result describeFleets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeFleetsAsync(array $args = [])
 * @method \Aws\Result describeImages(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeImagesAsync(array $args = [])
 * @method \Aws\Result describeSessions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeSessionsAsync(array $args = [])
 * @method \Aws\Result describeStacks(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeStacksAsync(array $args = [])
 * @method \Aws\Result disassociateFleet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise disassociateFleetAsync(array $args = [])
 * @method \Aws\Result expireSession(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise expireSessionAsync(array $args = [])
 * @method \Aws\Result listAssociatedFleets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listAssociatedFleetsAsync(array $args = [])
 * @method \Aws\Result listAssociatedStacks(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listAssociatedStacksAsync(array $args = [])
 * @method \Aws\Result startFleet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise startFleetAsync(array $args = [])
 * @method \Aws\Result stopFleet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise stopFleetAsync(array $args = [])
 * @method \Aws\Result updateFleet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateFleetAsync(array $args = [])
 * @method \Aws\Result updateStack(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateStackAsync(array $args = [])
 */
class AppstreamClient extends AwsClient {}
