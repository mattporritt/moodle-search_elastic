<?php
namespace Aws\ElasticTranscoder;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Elastic Transcoder** service.
 *
 * @method \Aws\Result cancelJob(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise cancelJobAsync(array $args = [])
 * @method \Aws\Result createJob(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createJobAsync(array $args = [])
 * @method \Aws\Result createPipeline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createPipelineAsync(array $args = [])
 * @method \Aws\Result createPreset(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createPresetAsync(array $args = [])
 * @method \Aws\Result deletePipeline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deletePipelineAsync(array $args = [])
 * @method \Aws\Result deletePreset(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deletePresetAsync(array $args = [])
 * @method \Aws\Result listJobsByPipeline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listJobsByPipelineAsync(array $args = [])
 * @method \Aws\Result listJobsByStatus(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listJobsByStatusAsync(array $args = [])
 * @method \Aws\Result listPipelines(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listPipelinesAsync(array $args = [])
 * @method \Aws\Result listPresets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listPresetsAsync(array $args = [])
 * @method \Aws\Result readJob(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise readJobAsync(array $args = [])
 * @method \Aws\Result readPipeline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise readPipelineAsync(array $args = [])
 * @method \Aws\Result readPreset(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise readPresetAsync(array $args = [])
 * @method \Aws\Result testRole(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise testRoleAsync(array $args = [])
 * @method \Aws\Result updatePipeline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updatePipelineAsync(array $args = [])
 * @method \Aws\Result updatePipelineNotifications(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updatePipelineNotificationsAsync(array $args = [])
 * @method \Aws\Result updatePipelineStatus(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updatePipelineStatusAsync(array $args = [])
 */
class ElasticTranscoderClient extends AwsClient {}
