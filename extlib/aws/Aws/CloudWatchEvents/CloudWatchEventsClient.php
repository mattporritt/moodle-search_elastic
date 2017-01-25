<?php
namespace Aws\CloudWatchEvents;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CloudWatch Events** service.
 *
 * @method \Aws\Result deleteRule(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteRuleAsync(array $args = [])
 * @method \Aws\Result describeRule(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeRuleAsync(array $args = [])
 * @method \Aws\Result disableRule(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise disableRuleAsync(array $args = [])
 * @method \Aws\Result enableRule(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise enableRuleAsync(array $args = [])
 * @method \Aws\Result listRuleNamesByTarget(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listRuleNamesByTargetAsync(array $args = [])
 * @method \Aws\Result listRules(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listRulesAsync(array $args = [])
 * @method \Aws\Result listTargetsByRule(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTargetsByRuleAsync(array $args = [])
 * @method \Aws\Result putEvents(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putEventsAsync(array $args = [])
 * @method \Aws\Result putRule(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putRuleAsync(array $args = [])
 * @method \Aws\Result putTargets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putTargetsAsync(array $args = [])
 * @method \Aws\Result removeTargets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise removeTargetsAsync(array $args = [])
 * @method \Aws\Result testEventPattern(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise testEventPatternAsync(array $args = [])
 */
class CloudWatchEventsClient extends AwsClient {}
