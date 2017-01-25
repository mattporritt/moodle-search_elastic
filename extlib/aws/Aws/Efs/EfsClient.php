<?php
namespace Aws\Efs;

use Aws\AwsClient;

/**
 * This client is used to interact with **Amazon EFS**.
 *
 * @method \Aws\Result createFileSystem(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createFileSystemAsync(array $args = [])
 * @method \Aws\Result createMountTarget(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createMountTargetAsync(array $args = [])
 * @method \Aws\Result createTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createTagsAsync(array $args = [])
 * @method \Aws\Result deleteFileSystem(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteFileSystemAsync(array $args = [])
 * @method \Aws\Result deleteMountTarget(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteMountTargetAsync(array $args = [])
 * @method \Aws\Result deleteTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteTagsAsync(array $args = [])
 * @method \Aws\Result describeFileSystems(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeFileSystemsAsync(array $args = [])
 * @method \Aws\Result describeMountTargetSecurityGroups(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeMountTargetSecurityGroupsAsync(array $args = [])
 * @method \Aws\Result describeMountTargets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeMountTargetsAsync(array $args = [])
 * @method \Aws\Result describeTags(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeTagsAsync(array $args = [])
 * @method \Aws\Result modifyMountTargetSecurityGroups(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise modifyMountTargetSecurityGroupsAsync(array $args = [])
 */
class EfsClient extends AwsClient {}
