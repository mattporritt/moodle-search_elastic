<?php
namespace Aws\KinesisAnalytics;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Kinesis Analytics** service.
 * @method \Aws\Result addApplicationInput(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise addApplicationInputAsync(array $args = [])
 * @method \Aws\Result addApplicationOutput(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise addApplicationOutputAsync(array $args = [])
 * @method \Aws\Result addApplicationReferenceDataSource(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise addApplicationReferenceDataSourceAsync(array $args = [])
 * @method \Aws\Result createApplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createApplicationAsync(array $args = [])
 * @method \Aws\Result deleteApplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteApplicationAsync(array $args = [])
 * @method \Aws\Result deleteApplicationOutput(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteApplicationOutputAsync(array $args = [])
 * @method \Aws\Result deleteApplicationReferenceDataSource(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteApplicationReferenceDataSourceAsync(array $args = [])
 * @method \Aws\Result describeApplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeApplicationAsync(array $args = [])
 * @method \Aws\Result discoverInputSchema(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise discoverInputSchemaAsync(array $args = [])
 * @method \Aws\Result listApplications(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listApplicationsAsync(array $args = [])
 * @method \Aws\Result startApplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise startApplicationAsync(array $args = [])
 * @method \Aws\Result stopApplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise stopApplicationAsync(array $args = [])
 * @method \Aws\Result updateApplication(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateApplicationAsync(array $args = [])
 */
class KinesisAnalyticsClient extends AwsClient {}
