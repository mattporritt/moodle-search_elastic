<?php
namespace Aws\Sms;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Server Migration Service** service.
 * @method \Aws\Result createReplicationJob(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createReplicationJobAsync(array $args = [])
 * @method \Aws\Result deleteReplicationJob(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteReplicationJobAsync(array $args = [])
 * @method \Aws\Result deleteServerCatalog(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteServerCatalogAsync(array $args = [])
 * @method \Aws\Result disassociateConnector(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise disassociateConnectorAsync(array $args = [])
 * @method \Aws\Result getConnectors(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getConnectorsAsync(array $args = [])
 * @method \Aws\Result getReplicationJobs(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getReplicationJobsAsync(array $args = [])
 * @method \Aws\Result getReplicationRuns(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getReplicationRunsAsync(array $args = [])
 * @method \Aws\Result getServers(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getServersAsync(array $args = [])
 * @method \Aws\Result importServerCatalog(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise importServerCatalogAsync(array $args = [])
 * @method \Aws\Result startOnDemandReplicationRun(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise startOnDemandReplicationRunAsync(array $args = [])
 * @method \Aws\Result updateReplicationJob(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateReplicationJobAsync(array $args = [])
 */
class SmsClient extends AwsClient {}
