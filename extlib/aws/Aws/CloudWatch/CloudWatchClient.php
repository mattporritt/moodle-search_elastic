<?php
namespace Aws\CloudWatch;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CloudWatch** service.
 *
 * @method \Aws\Result deleteAlarms(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteAlarmsAsync(array $args = [])
 * @method \Aws\Result describeAlarmHistory(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeAlarmHistoryAsync(array $args = [])
 * @method \Aws\Result describeAlarms(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeAlarmsAsync(array $args = [])
 * @method \Aws\Result describeAlarmsForMetric(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeAlarmsForMetricAsync(array $args = [])
 * @method \Aws\Result disableAlarmActions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise disableAlarmActionsAsync(array $args = [])
 * @method \Aws\Result enableAlarmActions(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise enableAlarmActionsAsync(array $args = [])
 * @method \Aws\Result getMetricStatistics(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise getMetricStatisticsAsync(array $args = [])
 * @method \Aws\Result listMetrics(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise listMetricsAsync(array $args = [])
 * @method \Aws\Result putMetricAlarm(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putMetricAlarmAsync(array $args = [])
 * @method \Aws\Result putMetricData(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise putMetricDataAsync(array $args = [])
 * @method \Aws\Result setAlarmState(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise setAlarmStateAsync(array $args = [])
 */
class CloudWatchClient extends AwsClient {}
