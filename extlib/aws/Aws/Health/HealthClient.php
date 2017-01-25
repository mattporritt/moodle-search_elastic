<?php
namespace Aws\Health;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Health APIs and Notifications** service.
 * @method \Aws\Result describeAffectedEntities(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeAffectedEntitiesAsync(array $args = [])
 * @method \Aws\Result describeEntityAggregates(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEntityAggregatesAsync(array $args = [])
 * @method \Aws\Result describeEventAggregates(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEventAggregatesAsync(array $args = [])
 * @method \Aws\Result describeEventDetails(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEventDetailsAsync(array $args = [])
 * @method \Aws\Result describeEventTypes(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEventTypesAsync(array $args = [])
 * @method \Aws\Result describeEvents(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEventsAsync(array $args = [])
 */
class HealthClient extends AwsClient {}
