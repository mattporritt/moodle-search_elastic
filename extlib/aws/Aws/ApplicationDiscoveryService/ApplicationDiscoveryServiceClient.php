<?php
namespace Aws\ApplicationDiscoveryService;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Application Discovery Service** service.
 * @method \Aws\Result associateConfigurationItemsToApplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise associateConfigurationItemsToApplicationAsync(array $args = [])
 * @method \Aws\Result createApplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createApplicationAsync(array $args = [])
 * @method \Aws\Result createTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createTagsAsync(array $args = [])
 * @method \Aws\Result deleteApplications(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteApplicationsAsync(array $args = [])
 * @method \Aws\Result deleteTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteTagsAsync(array $args = [])
 * @method \Aws\Result describeAgents(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeAgentsAsync(array $args = [])
 * @method \Aws\Result describeConfigurations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeConfigurationsAsync(array $args = [])
 * @method \Aws\Result describeExportConfigurations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeExportConfigurationsAsync(array $args = [])
 * @method \Aws\Result describeTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeTagsAsync(array $args = [])
 * @method \Aws\Result disassociateConfigurationItemsFromApplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise disassociateConfigurationItemsFromApplicationAsync(array $args = [])
 * @method \Aws\Result exportConfigurations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise exportConfigurationsAsync(array $args = [])
 * @method \Aws\Result getDiscoverySummary(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getDiscoverySummaryAsync(array $args = [])
 * @method \Aws\Result listConfigurations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listConfigurationsAsync(array $args = [])
 * @method \Aws\Result listServerNeighbors(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listServerNeighborsAsync(array $args = [])
 * @method \Aws\Result startDataCollectionByAgentIds(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise startDataCollectionByAgentIdsAsync(array $args = [])
 * @method \Aws\Result stopDataCollectionByAgentIds(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise stopDataCollectionByAgentIdsAsync(array $args = [])
 * @method \Aws\Result updateApplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateApplicationAsync(array $args = [])
 */
class ApplicationDiscoveryServiceClient extends AwsClient {}
