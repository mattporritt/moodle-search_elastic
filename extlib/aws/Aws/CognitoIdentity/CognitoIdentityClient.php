<?php
namespace Aws\CognitoIdentity;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Cognito Identity** service.
 *
 * @method \Aws\Result createIdentityPool(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createIdentityPoolAsync(array $args = [])
 * @method \Aws\Result deleteIdentities(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteIdentitiesAsync(array $args = [])
 * @method \Aws\Result deleteIdentityPool(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteIdentityPoolAsync(array $args = [])
 * @method \Aws\Result describeIdentity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeIdentityAsync(array $args = [])
 * @method \Aws\Result describeIdentityPool(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeIdentityPoolAsync(array $args = [])
 * @method \Aws\Result getCredentialsForIdentity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getCredentialsForIdentityAsync(array $args = [])
 * @method \Aws\Result getId(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getIdAsync(array $args = [])
 * @method \Aws\Result getIdentityPoolRoles(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getIdentityPoolRolesAsync(array $args = [])
 * @method \Aws\Result getOpenIdToken(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getOpenIdTokenAsync(array $args = [])
 * @method \Aws\Result getOpenIdTokenForDeveloperIdentity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getOpenIdTokenForDeveloperIdentityAsync(array $args = [])
 * @method \Aws\Result listIdentities(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listIdentitiesAsync(array $args = [])
 * @method \Aws\Result listIdentityPools(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listIdentityPoolsAsync(array $args = [])
 * @method \Aws\Result lookupDeveloperIdentity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise lookupDeveloperIdentityAsync(array $args = [])
 * @method \Aws\Result mergeDeveloperIdentities(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise mergeDeveloperIdentitiesAsync(array $args = [])
 * @method \Aws\Result setIdentityPoolRoles(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise setIdentityPoolRolesAsync(array $args = [])
 * @method \Aws\Result unlinkDeveloperIdentity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise unlinkDeveloperIdentityAsync(array $args = [])
 * @method \Aws\Result unlinkIdentity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise unlinkIdentityAsync(array $args = [])
 * @method \Aws\Result updateIdentityPool(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateIdentityPoolAsync(array $args = [])
 */
class CognitoIdentityClient extends AwsClient {}
