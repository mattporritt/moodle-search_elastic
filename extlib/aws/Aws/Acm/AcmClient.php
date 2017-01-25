<?php
namespace Aws\Acm;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Certificate Manager** service.
 *
 * @method \Aws\Result addTagsToCertificate(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise addTagsToCertificateAsync(array $args = [])
 * @method \Aws\Result deleteCertificate(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteCertificateAsync(array $args = [])
 * @method \Aws\Result describeCertificate(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeCertificateAsync(array $args = [])
 * @method \Aws\Result getCertificate(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getCertificateAsync(array $args = [])
 * @method \Aws\Result importCertificate(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise importCertificateAsync(array $args = [])
 * @method \Aws\Result listCertificates(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listCertificatesAsync(array $args = [])
 * @method \Aws\Result listTagsForCertificate(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listTagsForCertificateAsync(array $args = [])
 * @method \Aws\Result removeTagsFromCertificate(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise removeTagsFromCertificateAsync(array $args = [])
 * @method \Aws\Result requestCertificate(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise requestCertificateAsync(array $args = [])
 * @method \Aws\Result resendValidationEmail(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise resendValidationEmailAsync(array $args = [])
 */
class AcmClient extends AwsClient {}
