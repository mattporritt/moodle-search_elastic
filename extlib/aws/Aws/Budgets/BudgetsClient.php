<?php
namespace Aws\Budgets;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Budgets** service.
 * @method \Aws\Result createBudget(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createBudgetAsync(array $args = [])
 * @method \Aws\Result createNotification(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createNotificationAsync(array $args = [])
 * @method \Aws\Result createSubscriber(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise createSubscriberAsync(array $args = [])
 * @method \Aws\Result deleteBudget(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteBudgetAsync(array $args = [])
 * @method \Aws\Result deleteNotification(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteNotificationAsync(array $args = [])
 * @method \Aws\Result deleteSubscriber(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise deleteSubscriberAsync(array $args = [])
 * @method \Aws\Result describeBudget(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeBudgetAsync(array $args = [])
 * @method \Aws\Result describeBudgets(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeBudgetsAsync(array $args = [])
 * @method \Aws\Result describeNotificationsForBudget(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeNotificationsForBudgetAsync(array $args = [])
 * @method \Aws\Result describeSubscribersForNotification(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise describeSubscribersForNotificationAsync(array $args = [])
 * @method \Aws\Result updateBudget(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateBudgetAsync(array $args = [])
 * @method \Aws\Result updateNotification(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateNotificationAsync(array $args = [])
 * @method \Aws\Result updateSubscriber(array $args = [])
 * @method \GuzzleHttpv6\Promise\Promise updateSubscriberAsync(array $args = [])
 */
class BudgetsClient extends AwsClient {}
