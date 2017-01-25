<?php
namespace Aws\DataPipeline;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Data Pipeline** service.
 *
 * @method \Aws\Result activatePipeline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise activatePipelineAsync(array $args = [])
 * @method \Aws\Result addTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise addTagsAsync(array $args = [])
 * @method \Aws\Result createPipeline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createPipelineAsync(array $args = [])
 * @method \Aws\Result deactivatePipeline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deactivatePipelineAsync(array $args = [])
 * @method \Aws\Result deletePipeline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deletePipelineAsync(array $args = [])
 * @method \Aws\Result describeObjects(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeObjectsAsync(array $args = [])
 * @method \Aws\Result describePipelines(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describePipelinesAsync(array $args = [])
 * @method \Aws\Result evaluateExpression(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise evaluateExpressionAsync(array $args = [])
 * @method \Aws\Result getPipelineDefinition(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getPipelineDefinitionAsync(array $args = [])
 * @method \Aws\Result listPipelines(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listPipelinesAsync(array $args = [])
 * @method \Aws\Result pollForTask(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise pollForTaskAsync(array $args = [])
 * @method \Aws\Result putPipelineDefinition(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putPipelineDefinitionAsync(array $args = [])
 * @method \Aws\Result queryObjects(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise queryObjectsAsync(array $args = [])
 * @method \Aws\Result removeTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise removeTagsAsync(array $args = [])
 * @method \Aws\Result reportTaskProgress(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise reportTaskProgressAsync(array $args = [])
 * @method \Aws\Result reportTaskRunnerHeartbeat(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise reportTaskRunnerHeartbeatAsync(array $args = [])
 * @method \Aws\Result setStatus(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise setStatusAsync(array $args = [])
 * @method \Aws\Result setTaskStatus(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise setTaskStatusAsync(array $args = [])
 * @method \Aws\Result validatePipelineDefinition(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise validatePipelineDefinitionAsync(array $args = [])
 */
class DataPipelineClient extends AwsClient {}
