<?php
namespace Aws\Rds;

use Aws\AwsClient;
use Aws\Api\Service;
use Aws\Api\DocModel;
use Aws\Api\ApiProvider;
use Aws\PresignUrlMiddleware;
/**
 * This client is used to interact with the **Amazon Relational Database Service (Amazon RDS)**.
 *
 * @method \Aws\Result addSourceIdentifierToSubscription(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise addSourceIdentifierToSubscriptionAsync(array $args = [])
 * @method \Aws\Result addTagsToResource(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise addTagsToResourceAsync(array $args = [])
 * @method \Aws\Result authorizeDBSecurityGroupIngress(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise authorizeDBSecurityGroupIngressAsync(array $args = [])
 * @method \Aws\Result copyDBParameterGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise copyDBParameterGroupAsync(array $args = [])
 * @method \Aws\Result copyDBSnapshot(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise copyDBSnapshotAsync(array $args = [])
 * @method \Aws\Result copyOptionGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise copyOptionGroupAsync(array $args = [])
 * @method \Aws\Result createDBInstance(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createDBInstanceAsync(array $args = [])
 * @method \Aws\Result createDBInstanceReadReplica(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createDBInstanceReadReplicaAsync(array $args = [])
 * @method \Aws\Result createDBParameterGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createDBParameterGroupAsync(array $args = [])
 * @method \Aws\Result createDBSecurityGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createDBSecurityGroupAsync(array $args = [])
 * @method \Aws\Result createDBSnapshot(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createDBSnapshotAsync(array $args = [])
 * @method \Aws\Result createDBSubnetGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createDBSubnetGroupAsync(array $args = [])
 * @method \Aws\Result createEventSubscription(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createEventSubscriptionAsync(array $args = [])
 * @method \Aws\Result createOptionGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createOptionGroupAsync(array $args = [])
 * @method \Aws\Result deleteDBInstance(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteDBInstanceAsync(array $args = [])
 * @method \Aws\Result deleteDBParameterGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteDBParameterGroupAsync(array $args = [])
 * @method \Aws\Result deleteDBSecurityGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteDBSecurityGroupAsync(array $args = [])
 * @method \Aws\Result deleteDBSnapshot(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteDBSnapshotAsync(array $args = [])
 * @method \Aws\Result deleteDBSubnetGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteDBSubnetGroupAsync(array $args = [])
 * @method \Aws\Result deleteEventSubscription(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteEventSubscriptionAsync(array $args = [])
 * @method \Aws\Result deleteOptionGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteOptionGroupAsync(array $args = [])
 * @method \Aws\Result describeDBEngineVersions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeDBEngineVersionsAsync(array $args = [])
 * @method \Aws\Result describeDBInstances(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeDBInstancesAsync(array $args = [])
 * @method \Aws\Result describeDBLogFiles(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeDBLogFilesAsync(array $args = [])
 * @method \Aws\Result describeDBParameterGroups(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeDBParameterGroupsAsync(array $args = [])
 * @method \Aws\Result describeDBParameters(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeDBParametersAsync(array $args = [])
 * @method \Aws\Result describeDBSecurityGroups(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeDBSecurityGroupsAsync(array $args = [])
 * @method \Aws\Result describeDBSnapshots(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeDBSnapshotsAsync(array $args = [])
 * @method \Aws\Result describeDBSubnetGroups(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeDBSubnetGroupsAsync(array $args = [])
 * @method \Aws\Result describeEngineDefaultParameters(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEngineDefaultParametersAsync(array $args = [])
 * @method \Aws\Result describeEventCategories(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEventCategoriesAsync(array $args = [])
 * @method \Aws\Result describeEventSubscriptions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEventSubscriptionsAsync(array $args = [])
 * @method \Aws\Result describeEvents(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEventsAsync(array $args = [])
 * @method \Aws\Result describeOptionGroupOptions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeOptionGroupOptionsAsync(array $args = [])
 * @method \Aws\Result describeOptionGroups(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeOptionGroupsAsync(array $args = [])
 * @method \Aws\Result describeOrderableDBInstanceOptions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeOrderableDBInstanceOptionsAsync(array $args = [])
 * @method \Aws\Result describeReservedDBInstances(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeReservedDBInstancesAsync(array $args = [])
 * @method \Aws\Result describeReservedDBInstancesOfferings(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeReservedDBInstancesOfferingsAsync(array $args = [])
 * @method \Aws\Result downloadDBLogFilePortion(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise downloadDBLogFilePortionAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result modifyDBInstance(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise modifyDBInstanceAsync(array $args = [])
 * @method \Aws\Result modifyDBParameterGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise modifyDBParameterGroupAsync(array $args = [])
 * @method \Aws\Result modifyDBSubnetGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise modifyDBSubnetGroupAsync(array $args = [])
 * @method \Aws\Result modifyEventSubscription(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise modifyEventSubscriptionAsync(array $args = [])
 * @method \Aws\Result modifyOptionGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise modifyOptionGroupAsync(array $args = [])
 * @method \Aws\Result promoteReadReplica(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise promoteReadReplicaAsync(array $args = [])
 * @method \Aws\Result purchaseReservedDBInstancesOffering(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise purchaseReservedDBInstancesOfferingAsync(array $args = [])
 * @method \Aws\Result rebootDBInstance(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise rebootDBInstanceAsync(array $args = [])
 * @method \Aws\Result removeSourceIdentifierFromSubscription(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise removeSourceIdentifierFromSubscriptionAsync(array $args = [])
 * @method \Aws\Result removeTagsFromResource(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise removeTagsFromResourceAsync(array $args = [])
 * @method \Aws\Result resetDBParameterGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise resetDBParameterGroupAsync(array $args = [])
 * @method \Aws\Result restoreDBInstanceFromDBSnapshot(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise restoreDBInstanceFromDBSnapshotAsync(array $args = [])
 * @method \Aws\Result restoreDBInstanceToPointInTime(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise restoreDBInstanceToPointInTimeAsync(array $args = [])
 * @method \Aws\Result revokeDBSecurityGroupIngress(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise revokeDBSecurityGroupIngressAsync(array $args = [])
 * @method \Aws\Result addRoleToDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise addRoleToDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result applyPendingMaintenanceAction(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise applyPendingMaintenanceActionAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result copyDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise copyDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result copyDBClusterSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise copyDBClusterSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result createDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise createDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result createDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise createDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result createDBClusterSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise createDBClusterSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result deleteDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise deleteDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result deleteDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise deleteDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result deleteDBClusterSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise deleteDBClusterSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result describeAccountAttributes(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise describeAccountAttributesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result describeCertificates(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise describeCertificatesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result describeDBClusterParameterGroups(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise describeDBClusterParameterGroupsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result describeDBClusterParameters(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise describeDBClusterParametersAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result describeDBClusterSnapshotAttributes(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise describeDBClusterSnapshotAttributesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result describeDBClusterSnapshots(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise describeDBClusterSnapshotsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result describeDBClusters(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise describeDBClustersAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result describeDBSnapshotAttributes(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise describeDBSnapshotAttributesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result describeEngineDefaultClusterParameters(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise describeEngineDefaultClusterParametersAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result describePendingMaintenanceActions(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise describePendingMaintenanceActionsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result describeSourceRegions(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise describeSourceRegionsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result failoverDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise failoverDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result modifyDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise modifyDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result modifyDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise modifyDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result modifyDBClusterSnapshotAttribute(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise modifyDBClusterSnapshotAttributeAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result modifyDBSnapshotAttribute(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise modifyDBSnapshotAttributeAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result promoteReadReplicaDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise promoteReadReplicaDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result removeRoleFromDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise removeRoleFromDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result resetDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise resetDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result restoreDBClusterFromS3(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise restoreDBClusterFromS3Async(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result restoreDBClusterFromSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise restoreDBClusterFromSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \Aws\Result restoreDBClusterToPointInTime(array $args = []) (supported in versions 2014-10-31)
 * @method \GuzzleHttpv6\Promise\Promise restoreDBClusterToPointInTimeAsync(array $args = []) (supported in versions 2014-10-31)
 */
class RdsClient extends AwsClient
{
    public function __construct(array $args)
    {
        $args['with_resolved'] = function (array $args) {
            $this->getHandlerList()->appendInit(
                PresignUrlMiddleware::wrap(
                    $this,
                    $args['endpoint_provider'],
                    [
                        'operations' => [
                            'CopyDBSnapshot',
                        ],
                        'service' => 'rds',
                        'presign_param' => 'PreSignedUrl',
                    ]
                ),
                'rds.presigner'
            );
        };

        parent::__construct($args);
    }

    /**
     * @internal
     * @codeCoverageIgnore
     */
    public static function applyDocFilters(array $api, array $docs)
    {
        // Add the SourceRegion parameter
        $docs['shapes']['SourceRegion']['base'] = 'A required parameter that indicates '
            . 'the region that the DB snapshot will be copied from.';
        $api['shapes']['SourceRegion'] = ['type' => 'string'];
        $api['shapes']['CopyDBSnapshotMessage']['members']['SourceRegion'] = ['shape' => 'SourceRegion'];

        // Several parameters in presign APIs are optional.
        $docs['shapes']['String']['refs']['CopyDBSnapshotMessage$PreSignedUrl']
            = '<div class="alert alert-info">The SDK will compute this value '
            . 'for you on your behalf.</div>';
        $docs['shapes']['String']['refs']['CopyDBSnapshotMessage$DestinationRegion']
            = '<div class="alert alert-info">The SDK will populate this '
            . 'parameter on your behalf using the configured region value of '
            . 'the client.</div>';

        return [
            new Service($api, ApiProvider::defaultProvider()),
            new DocModel($docs)
        ];
    }
}
