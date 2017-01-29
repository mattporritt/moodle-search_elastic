<?php
namespace Aws\GameLift;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon GameLift** service.
 *
 * @method \Aws\Result createAlias(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createAliasAsync(array $args = [])
 * @method \Aws\Result createBuild(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createBuildAsync(array $args = [])
 * @method \Aws\Result createFleet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createFleetAsync(array $args = [])
 * @method \Aws\Result createGameSession(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createGameSessionAsync(array $args = [])
 * @method \Aws\Result createPlayerSession(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createPlayerSessionAsync(array $args = [])
 * @method \Aws\Result createPlayerSessions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createPlayerSessionsAsync(array $args = [])
 * @method \Aws\Result deleteAlias(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteAliasAsync(array $args = [])
 * @method \Aws\Result deleteBuild(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBuildAsync(array $args = [])
 * @method \Aws\Result deleteFleet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteFleetAsync(array $args = [])
 * @method \Aws\Result deleteScalingPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteScalingPolicyAsync(array $args = [])
 * @method \Aws\Result describeAlias(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeAliasAsync(array $args = [])
 * @method \Aws\Result describeBuild(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeBuildAsync(array $args = [])
 * @method \Aws\Result describeEC2InstanceLimits(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEC2InstanceLimitsAsync(array $args = [])
 * @method \Aws\Result describeFleetAttributes(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeFleetAttributesAsync(array $args = [])
 * @method \Aws\Result describeFleetCapacity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeFleetCapacityAsync(array $args = [])
 * @method \Aws\Result describeFleetEvents(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeFleetEventsAsync(array $args = [])
 * @method \Aws\Result describeFleetPortSettings(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeFleetPortSettingsAsync(array $args = [])
 * @method \Aws\Result describeFleetUtilization(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeFleetUtilizationAsync(array $args = [])
 * @method \Aws\Result describeGameSessionDetails(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeGameSessionDetailsAsync(array $args = [])
 * @method \Aws\Result describeGameSessions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeGameSessionsAsync(array $args = [])
 * @method \Aws\Result describeInstances(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeInstancesAsync(array $args = [])
 * @method \Aws\Result describePlayerSessions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describePlayerSessionsAsync(array $args = [])
 * @method \Aws\Result describeRuntimeConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeRuntimeConfigurationAsync(array $args = [])
 * @method \Aws\Result describeScalingPolicies(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeScalingPoliciesAsync(array $args = [])
 * @method \Aws\Result getGameSessionLogUrl(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getGameSessionLogUrlAsync(array $args = [])
 * @method \Aws\Result getInstanceAccess(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getInstanceAccessAsync(array $args = [])
 * @method \Aws\Result listAliases(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listAliasesAsync(array $args = [])
 * @method \Aws\Result listBuilds(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listBuildsAsync(array $args = [])
 * @method \Aws\Result listFleets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listFleetsAsync(array $args = [])
 * @method \Aws\Result putScalingPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putScalingPolicyAsync(array $args = [])
 * @method \Aws\Result requestUploadCredentials(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise requestUploadCredentialsAsync(array $args = [])
 * @method \Aws\Result resolveAlias(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise resolveAliasAsync(array $args = [])
 * @method \Aws\Result searchGameSessions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise searchGameSessionsAsync(array $args = [])
 * @method \Aws\Result updateAlias(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateAliasAsync(array $args = [])
 * @method \Aws\Result updateBuild(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateBuildAsync(array $args = [])
 * @method \Aws\Result updateFleetAttributes(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateFleetAttributesAsync(array $args = [])
 * @method \Aws\Result updateFleetCapacity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateFleetCapacityAsync(array $args = [])
 * @method \Aws\Result updateFleetPortSettings(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateFleetPortSettingsAsync(array $args = [])
 * @method \Aws\Result updateGameSession(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateGameSessionAsync(array $args = [])
 * @method \Aws\Result updateRuntimeConfiguration(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateRuntimeConfigurationAsync(array $args = [])
 */
class GameLiftClient extends AwsClient {}