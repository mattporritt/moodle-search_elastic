<?php
namespace Aws\Route53;

use Aws\AwsClient;
use Aws\CommandInterface;
use Psr\Http\Message\RequestInterface;

/**
 * This client is used to interact with the **Amazon Route 53** service.
 *
 * @method \Aws\Result associateVPCWithHostedZone(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise associateVPCWithHostedZoneAsync(array $args = [])
 * @method \Aws\Result changeResourceRecordSets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise changeResourceRecordSetsAsync(array $args = [])
 * @method \Aws\Result changeTagsForResource(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise changeTagsForResourceAsync(array $args = [])
 * @method \Aws\Result createHealthCheck(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createHealthCheckAsync(array $args = [])
 * @method \Aws\Result createHostedZone(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createHostedZoneAsync(array $args = [])
 * @method \Aws\Result createReusableDelegationSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createReusableDelegationSetAsync(array $args = [])
 * @method \Aws\Result createTrafficPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createTrafficPolicyAsync(array $args = [])
 * @method \Aws\Result createTrafficPolicyInstance(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createTrafficPolicyInstanceAsync(array $args = [])
 * @method \Aws\Result createTrafficPolicyVersion(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createTrafficPolicyVersionAsync(array $args = [])
 * @method \Aws\Result createVPCAssociationAuthorization(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createVPCAssociationAuthorizationAsync(array $args = [])
 * @method \Aws\Result deleteHealthCheck(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteHealthCheckAsync(array $args = [])
 * @method \Aws\Result deleteHostedZone(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteHostedZoneAsync(array $args = [])
 * @method \Aws\Result deleteReusableDelegationSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteReusableDelegationSetAsync(array $args = [])
 * @method \Aws\Result deleteTrafficPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteTrafficPolicyAsync(array $args = [])
 * @method \Aws\Result deleteTrafficPolicyInstance(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteTrafficPolicyInstanceAsync(array $args = [])
 * @method \Aws\Result deleteVPCAssociationAuthorization(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteVPCAssociationAuthorizationAsync(array $args = [])
 * @method \Aws\Result disassociateVPCFromHostedZone(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise disassociateVPCFromHostedZoneAsync(array $args = [])
 * @method \Aws\Result getChange(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getChangeAsync(array $args = [])
 * @method \Aws\Result getCheckerIpRanges(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getCheckerIpRangesAsync(array $args = [])
 * @method \Aws\Result getGeoLocation(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getGeoLocationAsync(array $args = [])
 * @method \Aws\Result getHealthCheck(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getHealthCheckAsync(array $args = [])
 * @method \Aws\Result getHealthCheckCount(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getHealthCheckCountAsync(array $args = [])
 * @method \Aws\Result getHealthCheckLastFailureReason(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getHealthCheckLastFailureReasonAsync(array $args = [])
 * @method \Aws\Result getHealthCheckStatus(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getHealthCheckStatusAsync(array $args = [])
 * @method \Aws\Result getHostedZone(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getHostedZoneAsync(array $args = [])
 * @method \Aws\Result getHostedZoneCount(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getHostedZoneCountAsync(array $args = [])
 * @method \Aws\Result getReusableDelegationSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getReusableDelegationSetAsync(array $args = [])
 * @method \Aws\Result getTrafficPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getTrafficPolicyAsync(array $args = [])
 * @method \Aws\Result getTrafficPolicyInstance(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getTrafficPolicyInstanceAsync(array $args = [])
 * @method \Aws\Result getTrafficPolicyInstanceCount(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getTrafficPolicyInstanceCountAsync(array $args = [])
 * @method \Aws\Result listGeoLocations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listGeoLocationsAsync(array $args = [])
 * @method \Aws\Result listHealthChecks(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listHealthChecksAsync(array $args = [])
 * @method \Aws\Result listHostedZones(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listHostedZonesAsync(array $args = [])
 * @method \Aws\Result listHostedZonesByName(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listHostedZonesByNameAsync(array $args = [])
 * @method \Aws\Result listResourceRecordSets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listResourceRecordSetsAsync(array $args = [])
 * @method \Aws\Result listReusableDelegationSets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listReusableDelegationSetsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result listTagsForResources(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTagsForResourcesAsync(array $args = [])
 * @method \Aws\Result listTrafficPolicies(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTrafficPoliciesAsync(array $args = [])
 * @method \Aws\Result listTrafficPolicyInstances(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTrafficPolicyInstancesAsync(array $args = [])
 * @method \Aws\Result listTrafficPolicyInstancesByHostedZone(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTrafficPolicyInstancesByHostedZoneAsync(array $args = [])
 * @method \Aws\Result listTrafficPolicyInstancesByPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTrafficPolicyInstancesByPolicyAsync(array $args = [])
 * @method \Aws\Result listTrafficPolicyVersions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTrafficPolicyVersionsAsync(array $args = [])
 * @method \Aws\Result listVPCAssociationAuthorizations(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listVPCAssociationAuthorizationsAsync(array $args = [])
 * @method \Aws\Result testDNSAnswer(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise testDNSAnswerAsync(array $args = [])
 * @method \Aws\Result updateHealthCheck(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateHealthCheckAsync(array $args = [])
 * @method \Aws\Result updateHostedZoneComment(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateHostedZoneCommentAsync(array $args = [])
 * @method \Aws\Result updateTrafficPolicyComment(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateTrafficPolicyCommentAsync(array $args = [])
 * @method \Aws\Result updateTrafficPolicyInstance(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateTrafficPolicyInstanceAsync(array $args = [])
 */
class Route53Client extends AwsClient
{
    public function __construct(array $args)
    {
        parent::__construct($args);
        $this->getHandlerList()->appendInit($this->cleanIdFn(), 'route53.clean_id');
    }

    private function cleanIdFn()
    {
        return function (callable $handler) {
            return function (CommandInterface $c, RequestInterface $r = null) use ($handler) {
                foreach (['Id', 'HostedZoneId', 'DelegationSetId'] as $clean) {
                    if ($c->hasParam($clean)) {
                        $c[$clean] = $this->cleanId($c[$clean]);
                    }
                }
                return $handler($c, $r);
            };
        };
    }

    private function cleanId($id)
    {
        static $toClean = ['/hostedzone/', '/change/', '/delegationset/'];

        return str_replace($toClean, '', $id);
    }
}
