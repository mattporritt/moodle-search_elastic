<?php
namespace Aws\CodeCommit;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS CodeCommit** service.
 *
 * @method \Aws\Result batchGetRepositories(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise batchGetRepositoriesAsync(array $args = [])
 * @method \Aws\Result createBranch(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createBranchAsync(array $args = [])
 * @method \Aws\Result createRepository(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createRepositoryAsync(array $args = [])
 * @method \Aws\Result deleteRepository(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteRepositoryAsync(array $args = [])
 * @method \Aws\Result getBranch(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getBranchAsync(array $args = [])
 * @method \Aws\Result getCommit(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getCommitAsync(array $args = [])
 * @method \Aws\Result getRepository(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getRepositoryAsync(array $args = [])
 * @method \Aws\Result getRepositoryTriggers(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getRepositoryTriggersAsync(array $args = [])
 * @method \Aws\Result listBranches(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listBranchesAsync(array $args = [])
 * @method \Aws\Result listRepositories(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listRepositoriesAsync(array $args = [])
 * @method \Aws\Result putRepositoryTriggers(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putRepositoryTriggersAsync(array $args = [])
 * @method \Aws\Result testRepositoryTriggers(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise testRepositoryTriggersAsync(array $args = [])
 * @method \Aws\Result updateDefaultBranch(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateDefaultBranchAsync(array $args = [])
 * @method \Aws\Result updateRepositoryDescription(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateRepositoryDescriptionAsync(array $args = [])
 * @method \Aws\Result updateRepositoryName(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateRepositoryNameAsync(array $args = [])
 */
class CodeCommitClient extends AwsClient {}
