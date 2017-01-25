<?php
namespace Aws\Sfn;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Step Functions** service.
 * @method \Aws\Result createActivity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createActivityAsync(array $args = [])
 * @method \Aws\Result createStateMachine(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createStateMachineAsync(array $args = [])
 * @method \Aws\Result deleteActivity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteActivityAsync(array $args = [])
 * @method \Aws\Result deleteStateMachine(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteStateMachineAsync(array $args = [])
 * @method \Aws\Result describeActivity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeActivityAsync(array $args = [])
 * @method \Aws\Result describeExecution(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeExecutionAsync(array $args = [])
 * @method \Aws\Result describeStateMachine(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeStateMachineAsync(array $args = [])
 * @method \Aws\Result getActivityTask(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getActivityTaskAsync(array $args = [])
 * @method \Aws\Result getExecutionHistory(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getExecutionHistoryAsync(array $args = [])
 * @method \Aws\Result listActivities(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listActivitiesAsync(array $args = [])
 * @method \Aws\Result listExecutions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listExecutionsAsync(array $args = [])
 * @method \Aws\Result listStateMachines(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listStateMachinesAsync(array $args = [])
 * @method \Aws\Result sendTaskFailure(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise sendTaskFailureAsync(array $args = [])
 * @method \Aws\Result sendTaskHeartbeat(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise sendTaskHeartbeatAsync(array $args = [])
 * @method \Aws\Result sendTaskSuccess(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise sendTaskSuccessAsync(array $args = [])
 * @method \Aws\Result startExecution(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise startExecutionAsync(array $args = [])
 * @method \Aws\Result stopExecution(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise stopExecutionAsync(array $args = [])
 */
class SfnClient extends AwsClient {}
