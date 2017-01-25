<?php
namespace Aws\Ssm;

use Aws\AwsClient;
use Aws\IdempotencyTokenMiddleware;

/**
 * Amazon EC2 Simple Systems Manager client.
 *
 * @method \Aws\Result addTagsToResource(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise addTagsToResourceAsync(array $args = [])
 * @method \Aws\Result cancelCommand(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise cancelCommandAsync(array $args = [])
 * @method \Aws\Result createActivation(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createActivationAsync(array $args = [])
 * @method \Aws\Result createAssociation(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createAssociationAsync(array $args = [])
 * @method \Aws\Result createAssociationBatch(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createAssociationBatchAsync(array $args = [])
 * @method \Aws\Result createDocument(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createDocumentAsync(array $args = [])
 * @method \Aws\Result createMaintenanceWindow(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createMaintenanceWindowAsync(array $args = [])
 * @method \Aws\Result createPatchBaseline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createPatchBaselineAsync(array $args = [])
 * @method \Aws\Result deleteActivation(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteActivationAsync(array $args = [])
 * @method \Aws\Result deleteAssociation(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteAssociationAsync(array $args = [])
 * @method \Aws\Result deleteDocument(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteDocumentAsync(array $args = [])
 * @method \Aws\Result deleteMaintenanceWindow(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteMaintenanceWindowAsync(array $args = [])
 * @method \Aws\Result deleteParameter(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteParameterAsync(array $args = [])
 * @method \Aws\Result deletePatchBaseline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deletePatchBaselineAsync(array $args = [])
 * @method \Aws\Result deregisterManagedInstance(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deregisterManagedInstanceAsync(array $args = [])
 * @method \Aws\Result deregisterPatchBaselineForPatchGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deregisterPatchBaselineForPatchGroupAsync(array $args = [])
 * @method \Aws\Result deregisterTargetFromMaintenanceWindow(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deregisterTargetFromMaintenanceWindowAsync(array $args = [])
 * @method \Aws\Result deregisterTaskFromMaintenanceWindow(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deregisterTaskFromMaintenanceWindowAsync(array $args = [])
 * @method \Aws\Result describeActivations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeActivationsAsync(array $args = [])
 * @method \Aws\Result describeAssociation(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeAssociationAsync(array $args = [])
 * @method \Aws\Result describeAutomationExecutions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeAutomationExecutionsAsync(array $args = [])
 * @method \Aws\Result describeAvailablePatches(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeAvailablePatchesAsync(array $args = [])
 * @method \Aws\Result describeDocument(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeDocumentAsync(array $args = [])
 * @method \Aws\Result describeDocumentPermission(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeDocumentPermissionAsync(array $args = [])
 * @method \Aws\Result describeEffectiveInstanceAssociations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEffectiveInstanceAssociationsAsync(array $args = [])
 * @method \Aws\Result describeEffectivePatchesForPatchBaseline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeEffectivePatchesForPatchBaselineAsync(array $args = [])
 * @method \Aws\Result describeInstanceAssociationsStatus(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeInstanceAssociationsStatusAsync(array $args = [])
 * @method \Aws\Result describeInstanceInformation(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeInstanceInformationAsync(array $args = [])
 * @method \Aws\Result describeInstancePatchStates(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeInstancePatchStatesAsync(array $args = [])
 * @method \Aws\Result describeInstancePatchStatesForPatchGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeInstancePatchStatesForPatchGroupAsync(array $args = [])
 * @method \Aws\Result describeInstancePatches(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeInstancePatchesAsync(array $args = [])
 * @method \Aws\Result describeMaintenanceWindowExecutionTaskInvocations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeMaintenanceWindowExecutionTaskInvocationsAsync(array $args = [])
 * @method \Aws\Result describeMaintenanceWindowExecutionTasks(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeMaintenanceWindowExecutionTasksAsync(array $args = [])
 * @method \Aws\Result describeMaintenanceWindowExecutions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeMaintenanceWindowExecutionsAsync(array $args = [])
 * @method \Aws\Result describeMaintenanceWindowTargets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeMaintenanceWindowTargetsAsync(array $args = [])
 * @method \Aws\Result describeMaintenanceWindowTasks(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeMaintenanceWindowTasksAsync(array $args = [])
 * @method \Aws\Result describeMaintenanceWindows(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeMaintenanceWindowsAsync(array $args = [])
 * @method \Aws\Result describeParameters(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeParametersAsync(array $args = [])
 * @method \Aws\Result describePatchBaselines(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describePatchBaselinesAsync(array $args = [])
 * @method \Aws\Result describePatchGroupState(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describePatchGroupStateAsync(array $args = [])
 * @method \Aws\Result describePatchGroups(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describePatchGroupsAsync(array $args = [])
 * @method \Aws\Result getAutomationExecution(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getAutomationExecutionAsync(array $args = [])
 * @method \Aws\Result getCommandInvocation(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getCommandInvocationAsync(array $args = [])
 * @method \Aws\Result getDefaultPatchBaseline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getDefaultPatchBaselineAsync(array $args = [])
 * @method \Aws\Result getDeployablePatchSnapshotForInstance(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getDeployablePatchSnapshotForInstanceAsync(array $args = [])
 * @method \Aws\Result getDocument(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getDocumentAsync(array $args = [])
 * @method \Aws\Result getInventory(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getInventoryAsync(array $args = [])
 * @method \Aws\Result getInventorySchema(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getInventorySchemaAsync(array $args = [])
 * @method \Aws\Result getMaintenanceWindow(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getMaintenanceWindowAsync(array $args = [])
 * @method \Aws\Result getMaintenanceWindowExecution(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getMaintenanceWindowExecutionAsync(array $args = [])
 * @method \Aws\Result getMaintenanceWindowExecutionTask(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getMaintenanceWindowExecutionTaskAsync(array $args = [])
 * @method \Aws\Result getParameterHistory(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getParameterHistoryAsync(array $args = [])
 * @method \Aws\Result getParameters(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getParametersAsync(array $args = [])
 * @method \Aws\Result getPatchBaseline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getPatchBaselineAsync(array $args = [])
 * @method \Aws\Result getPatchBaselineForPatchGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getPatchBaselineForPatchGroupAsync(array $args = [])
 * @method \Aws\Result listAssociations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listAssociationsAsync(array $args = [])
 * @method \Aws\Result listCommandInvocations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listCommandInvocationsAsync(array $args = [])
 * @method \Aws\Result listCommands(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listCommandsAsync(array $args = [])
 * @method \Aws\Result listDocumentVersions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listDocumentVersionsAsync(array $args = [])
 * @method \Aws\Result listDocuments(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listDocumentsAsync(array $args = [])
 * @method \Aws\Result listInventoryEntries(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listInventoryEntriesAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result modifyDocumentPermission(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise modifyDocumentPermissionAsync(array $args = [])
 * @method \Aws\Result putInventory(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putInventoryAsync(array $args = [])
 * @method \Aws\Result putParameter(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putParameterAsync(array $args = [])
 * @method \Aws\Result registerDefaultPatchBaseline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise registerDefaultPatchBaselineAsync(array $args = [])
 * @method \Aws\Result registerPatchBaselineForPatchGroup(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise registerPatchBaselineForPatchGroupAsync(array $args = [])
 * @method \Aws\Result registerTargetWithMaintenanceWindow(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise registerTargetWithMaintenanceWindowAsync(array $args = [])
 * @method \Aws\Result registerTaskWithMaintenanceWindow(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise registerTaskWithMaintenanceWindowAsync(array $args = [])
 * @method \Aws\Result removeTagsFromResource(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise removeTagsFromResourceAsync(array $args = [])
 * @method \Aws\Result sendCommand(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise sendCommandAsync(array $args = [])
 * @method \Aws\Result startAutomationExecution(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise startAutomationExecutionAsync(array $args = [])
 * @method \Aws\Result stopAutomationExecution(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise stopAutomationExecutionAsync(array $args = [])
 * @method \Aws\Result updateAssociation(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateAssociationAsync(array $args = [])
 * @method \Aws\Result updateAssociationStatus(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateAssociationStatusAsync(array $args = [])
 * @method \Aws\Result updateDocument(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateDocumentAsync(array $args = [])
 * @method \Aws\Result updateDocumentDefaultVersion(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateDocumentDefaultVersionAsync(array $args = [])
 * @method \Aws\Result updateMaintenanceWindow(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateMaintenanceWindowAsync(array $args = [])
 * @method \Aws\Result updateManagedInstanceRole(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateManagedInstanceRoleAsync(array $args = [])
 * @method \Aws\Result updatePatchBaseline(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updatePatchBaselineAsync(array $args = [])
 */
class SsmClient extends AwsClient
{
    public static function getArguments()
    {
        $args = parent::getArguments();
        return $args + [
            'idempotency_auto_fill' => [
                'type'    => 'config',
                'valid'   => ['bool'],
                'doc'     => 'Set to false to disable SDK to populate parameters that'
                    . ' enabled \'idempotencyToken\' trait with a random UUID v4'
                    . ' value on your behalf. Using default value \'true\' still allows'
                    . ' parameter value to be overwritten when provided. Note:'
                    . ' auto-fill only works when cryptographically secure random'
                    . ' bytes generator functions(random_bytes, openssl_random_pseudo_bytes'
                    . ' or mcrypt_create_iv) can be found.',
                'default' => true,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * In addition to the options available to
     * {@see Aws\AwsClient::__construct}, SsmClient accepts the following
     * options:
     *
     * - idempotency_auto_fill: (bool) Set to false to disable SDK to populate
     *   parameters that enabled 'idempotencyToken' trait with a default UUID v4
     *   value on your behalf. Using default value 'true' still allows parameter
     *   value to be overwritten when provided.
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        parent::__construct($args);
        if ($this->getConfig('idempotency_auto_fill')) {
            $stack = $this->getHandlerList();
            $stack->prependInit(
                IdempotencyTokenMiddleware::wrap($this->getApi()),
                'ssm.idempotency_auto_fill'
            );
        }
    }
}
