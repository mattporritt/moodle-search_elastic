<?php
namespace Aws\WorkSpaces;

use Aws\AwsClient;

/**
 * Amazon WorkSpaces client.
 *
 * @method \Aws\Result createTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createTagsAsync(array $args = [])
 * @method \Aws\Result createWorkspaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createWorkspacesAsync(array $args = [])
 * @method \Aws\Result deleteTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteTagsAsync(array $args = [])
 * @method \Aws\Result describeTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeTagsAsync(array $args = [])
 * @method \Aws\Result describeWorkspaceBundles(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeWorkspaceBundlesAsync(array $args = [])
 * @method \Aws\Result describeWorkspaceDirectories(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeWorkspaceDirectoriesAsync(array $args = [])
 * @method \Aws\Result describeWorkspaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeWorkspacesAsync(array $args = [])
 * @method \Aws\Result describeWorkspacesConnectionStatus(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeWorkspacesConnectionStatusAsync(array $args = [])
 * @method \Aws\Result modifyWorkspaceProperties(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise modifyWorkspacePropertiesAsync(array $args = [])
 * @method \Aws\Result rebootWorkspaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise rebootWorkspacesAsync(array $args = [])
 * @method \Aws\Result rebuildWorkspaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise rebuildWorkspacesAsync(array $args = [])
 * @method \Aws\Result startWorkspaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise startWorkspacesAsync(array $args = [])
 * @method \Aws\Result stopWorkspaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise stopWorkspacesAsync(array $args = [])
 * @method \Aws\Result terminateWorkspaces(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise terminateWorkspacesAsync(array $args = [])
 */
class WorkSpacesClient extends AwsClient {}
