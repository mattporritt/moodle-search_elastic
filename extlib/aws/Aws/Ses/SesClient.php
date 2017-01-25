<?php
namespace Aws\Ses;

use Aws\Credentials\CredentialsInterface;

/**
 * This client is used to interact with the **Amazon Simple Email Service (Amazon SES)**.
 *
 * @method \Aws\Result cloneReceiptRuleSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise cloneReceiptRuleSetAsync(array $args = [])
 * @method \Aws\Result createConfigurationSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createConfigurationSetAsync(array $args = [])
 * @method \Aws\Result createConfigurationSetEventDestination(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createConfigurationSetEventDestinationAsync(array $args = [])
 * @method \Aws\Result createReceiptFilter(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createReceiptFilterAsync(array $args = [])
 * @method \Aws\Result createReceiptRule(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createReceiptRuleAsync(array $args = [])
 * @method \Aws\Result createReceiptRuleSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createReceiptRuleSetAsync(array $args = [])
 * @method \Aws\Result deleteConfigurationSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteConfigurationSetAsync(array $args = [])
 * @method \Aws\Result deleteConfigurationSetEventDestination(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteConfigurationSetEventDestinationAsync(array $args = [])
 * @method \Aws\Result deleteIdentity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteIdentityAsync(array $args = [])
 * @method \Aws\Result deleteIdentityPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteIdentityPolicyAsync(array $args = [])
 * @method \Aws\Result deleteReceiptFilter(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteReceiptFilterAsync(array $args = [])
 * @method \Aws\Result deleteReceiptRule(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteReceiptRuleAsync(array $args = [])
 * @method \Aws\Result deleteReceiptRuleSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteReceiptRuleSetAsync(array $args = [])
 * @method \Aws\Result deleteVerifiedEmailAddress(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteVerifiedEmailAddressAsync(array $args = [])
 * @method \Aws\Result describeActiveReceiptRuleSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeActiveReceiptRuleSetAsync(array $args = [])
 * @method \Aws\Result describeConfigurationSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeConfigurationSetAsync(array $args = [])
 * @method \Aws\Result describeReceiptRule(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeReceiptRuleAsync(array $args = [])
 * @method \Aws\Result describeReceiptRuleSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeReceiptRuleSetAsync(array $args = [])
 * @method \Aws\Result getIdentityDkimAttributes(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getIdentityDkimAttributesAsync(array $args = [])
 * @method \Aws\Result getIdentityMailFromDomainAttributes(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getIdentityMailFromDomainAttributesAsync(array $args = [])
 * @method \Aws\Result getIdentityNotificationAttributes(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getIdentityNotificationAttributesAsync(array $args = [])
 * @method \Aws\Result getIdentityPolicies(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getIdentityPoliciesAsync(array $args = [])
 * @method \Aws\Result getIdentityVerificationAttributes(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getIdentityVerificationAttributesAsync(array $args = [])
 * @method \Aws\Result getSendQuota(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getSendQuotaAsync(array $args = [])
 * @method \Aws\Result getSendStatistics(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getSendStatisticsAsync(array $args = [])
 * @method \Aws\Result listConfigurationSets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listConfigurationSetsAsync(array $args = [])
 * @method \Aws\Result listIdentities(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listIdentitiesAsync(array $args = [])
 * @method \Aws\Result listIdentityPolicies(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listIdentityPoliciesAsync(array $args = [])
 * @method \Aws\Result listReceiptFilters(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listReceiptFiltersAsync(array $args = [])
 * @method \Aws\Result listReceiptRuleSets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listReceiptRuleSetsAsync(array $args = [])
 * @method \Aws\Result listVerifiedEmailAddresses(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listVerifiedEmailAddressesAsync(array $args = [])
 * @method \Aws\Result putIdentityPolicy(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putIdentityPolicyAsync(array $args = [])
 * @method \Aws\Result reorderReceiptRuleSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise reorderReceiptRuleSetAsync(array $args = [])
 * @method \Aws\Result sendBounce(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise sendBounceAsync(array $args = [])
 * @method \Aws\Result sendEmail(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise sendEmailAsync(array $args = [])
 * @method \Aws\Result sendRawEmail(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise sendRawEmailAsync(array $args = [])
 * @method \Aws\Result setActiveReceiptRuleSet(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise setActiveReceiptRuleSetAsync(array $args = [])
 * @method \Aws\Result setIdentityDkimEnabled(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise setIdentityDkimEnabledAsync(array $args = [])
 * @method \Aws\Result setIdentityFeedbackForwardingEnabled(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise setIdentityFeedbackForwardingEnabledAsync(array $args = [])
 * @method \Aws\Result setIdentityHeadersInNotificationsEnabled(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise setIdentityHeadersInNotificationsEnabledAsync(array $args = [])
 * @method \Aws\Result setIdentityMailFromDomain(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise setIdentityMailFromDomainAsync(array $args = [])
 * @method \Aws\Result setIdentityNotificationTopic(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise setIdentityNotificationTopicAsync(array $args = [])
 * @method \Aws\Result setReceiptRulePosition(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise setReceiptRulePositionAsync(array $args = [])
 * @method \Aws\Result updateConfigurationSetEventDestination(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateConfigurationSetEventDestinationAsync(array $args = [])
 * @method \Aws\Result updateReceiptRule(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateReceiptRuleAsync(array $args = [])
 * @method \Aws\Result verifyDomainDkim(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise verifyDomainDkimAsync(array $args = [])
 * @method \Aws\Result verifyDomainIdentity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise verifyDomainIdentityAsync(array $args = [])
 * @method \Aws\Result verifyEmailAddress(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise verifyEmailAddressAsync(array $args = [])
 * @method \Aws\Result verifyEmailIdentity(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise verifyEmailIdentityAsync(array $args = [])
 */
class SesClient extends \Aws\AwsClient
{
    /**
     * Create an SMTP password for a given IAM user's credentials.
     *
     * The SMTP username is the Access Key ID for the provided credentials.
     *
     * @link http://docs.aws.amazon.com/ses/latest/DeveloperGuide/smtp-credentials.html#smtp-credentials-convert
     *
     * @param CredentialsInterface $creds
     *
     * @return string
     */
    public static function generateSmtpPassword(CredentialsInterface $creds)
    {
        static $version = "\x02";
        static $algo = 'sha256';
        static $message = 'SendRawEmail';
        $signature = hash_hmac($algo, $message, $creds->getSecretKey(), true);

        return base64_encode($version . $signature);
    }
}
