<?php
namespace Aws\OpsWorksCM;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS OpsWorks for Chef Automate** service.
 * @method \Aws\Result associateNode(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise associateNodeAsync(array $args = [])
 * @method \Aws\Result createBackup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createBackupAsync(array $args = [])
 * @method \Aws\Result createServer(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createServerAsync(array $args = [])
 * @method \Aws\Result deleteBackup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBackupAsync(array $args = [])
 * @method \Aws\Result deleteServer(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteServerAsync(array $args = [])
 * @method \Aws\Result describeAccountAttributes(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeAccountAttributesAsync(array $args = [])
 * @method \Aws\Result describeBackups(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeBackupsAsync(array $args = [])
 * @method \Aws\Result describeEvents(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEventsAsync(array $args = [])
 * @method \Aws\Result describeNodeAssociationStatus(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeNodeAssociationStatusAsync(array $args = [])
 * @method \Aws\Result describeServers(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeServersAsync(array $args = [])
 * @method \Aws\Result disassociateNode(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise disassociateNodeAsync(array $args = [])
 * @method \Aws\Result restoreServer(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise restoreServerAsync(array $args = [])
 * @method \Aws\Result startMaintenance(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise startMaintenanceAsync(array $args = [])
 * @method \Aws\Result updateServer(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateServerAsync(array $args = [])
 * @method \Aws\Result updateServerEngineAttributes(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateServerEngineAttributesAsync(array $args = [])
 */
class OpsWorksCMClient extends AwsClient {}
