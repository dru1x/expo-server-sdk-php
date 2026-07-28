# Expo Push Server SDK (PHP)

![Test Workflow Status](https://github.com/Dru1X/expo-server-sdk-php/workflows/Test/badge.svg)

Server-side library for working with the Expo Push service using PHP 8.2+

## ⚙ Installation

### Requirements

- [PHP 8.2+](https://php.net/releases)
- [PHP Zlib extension](https://www.php.net/manual/en/book.zlib.php)

### Instructions

Install the library with composer:

```shell
composer require dru1x/expo-server-sdk-php 
```

## 🚀 Usage

First, instantiate the `ExpoPush` service:

```php
use Dru1x\ExpoPush\ExpoPush;

$expoPush = new ExpoPush();
```

If [additional security](https://docs.expo.dev/push-notifications/sending-notifications/#additional-security) is being 
used, the access token can be supplied as an argument to the constructor:

```php
use Dru1x\ExpoPush\ExpoPush;

$accessToken = 'NTLyMHB2vtZ1lWhgP0sjWJTOCed9zspT';
$expoPush    = new ExpoPush($accessToken);
```

### Retrying Transient Failures

By default, a request that fails due to a network issue (e.g. a timeout or connection error) or
a `5xx` server error is not retried. To have such transient failures retried automatically,
supply a `RetryConfig` to the constructor:

```php
use Dru1x\ExpoPush\ExpoPush;
use Dru1x\ExpoPush\Config\RetryConfig;

$expoPush = new ExpoPush(retryConfig: new RetryConfig(
    tries: 3,
    retryInterval: 500,
    useExponentialBackoff: true,
));
```

- `tries` — the maximum number of attempts to make for a single request.
- `retryInterval` — the base delay between retries, in milliseconds.
- `useExponentialBackoff` — whether the delay should double after each attempt.
- `throwOnMaxTries` — whether an exception should be thrown once retries are exhausted
  (defaults to `true`); if set to `false`, the last error response is returned instead.

Client errors (e.g. `4xx` responses) are never retried, since retrying is unlikely to change the
outcome. If all retries are exhausted, the failure is recorded as a `PushError`, the same as if
retries were never configured.

### Sending Push Notifications

Push notifications can be sent by supplying a `PushMessageCollection`, or an array of `PushMessage` objects, to the
`sendNotifications()` method. This automatically chunks the given push messages into an appropriate number of requests 
and sends them concurrently to Expo's Push API. The max request size, concurrency limit and rate limit are applied as 
set out in [Expo's Push API documentation](https://docs.expo.dev/push-notifications/sending-notifications/#http2-api). 
Therefore, the maximum throughput of this method is 600 notifications per second. 

```php
use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushMessage\PushMessageCollection;
use Dru1x\ExpoPush\PushMessage\PushMessage;
use Dru1x\ExpoPush\PushTicket\PushTicketCollection;
use Dru1x\ExpoPush\PushToken\PushToken;

// This could also be an array of PushMessage objects
$messages = new PushMessageCollection(
    new PushMessage(
        to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
        title: 'Hello',
        body: 'This is a push notification' 
    )
);

$result = $expoPush->sendNotifications($messages);

/** @var PushTicketCollection $tickets */
$tickets = $result->tickets;

/** @var PushErrorCollection|null $errors */
$errors = $result->errors;
```
The `SendNotificationsResult` object returned by `sendNotifications()` contains a collection of all the resulting 
`PushTicket` objects, as well as a collection of `PushError` objects representing any 
[request-level errors](https://docs.expo.dev/push-notifications/sending-notifications/#request-errors) encountered while 
sending the given batch of notifications.

The `PushTicketCollection` is ordered according to the order of the `PushMessage` objects passed in to 
`sendNotifications()`. Each `PushTicket` will either be a `SuccessfulPushTicket` or a `FailedPushTicket`, the latter 
representing a ticket that was returned with a status of "error".

If errors were encountered, they will be present in the `PushErrorCollection`, and the `PushTicketCollection` will have 
a gap in its keys that corresponds to the failed chunk of notifications. Inspect the errors to find out what went wrong.

### Checking Tickets

Once notifications have been sent, Expo recommends that 
[notification outcomes are checked](https://docs.expo.dev/push-notifications/sending-notifications/#check-push-receipts-for-errors). 
This is done by fetching `PushReceipt` objects, each identified by an ID included in a previously returned `PushTicket`.

Receipts can be fetched by supplying a `PushReceiptIdCollection`, or an array of push receipt ID strings, to the 
`getReceipts()` method. This also automatically chunks the given receipt IDs into an appropriate number of requests 
and sends them concurrently to the Expo Push API.

Expo recommends that this is done between 15 minutes and 24 hours after notifications were sent.

```php
use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushReceipt\PushReceiptIdCollection;
use Dru1x\ExpoPush\PushReceipt\PushReceiptCollection;

// This could also be an array of receipt ID strings
$receiptIds = new PushReceiptIdCollection(
    'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
    'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
);

$result = $expoPush->getReceipts($receiptIds);

/** @var PushReceiptCollection $receipts */
$receipts = $result->receipts;

/** @var PushErrorCollection|null $errors */
$errors = $result->errors;
```

The `GetReceiptsResult` object returned by `getReceipts()` contains a collection of the resulting `PushReceipt` objects,
as well as a collection of `PushError` objects representing any 
[request-level errors](https://docs.expo.dev/push-notifications/sending-notifications/#request-errors) encountered while 
getting the given batch of receipts.

The `PushReceiptCollection` respects the order of receipts returned by the Expo Push API. To find a specific receipt in 
the collection, the `getById()` method can be used. Each `PushReceipt` with either be a `SuccessfulPushReceipt` or a 
`FailedPushReceipt`, the latter representing a receipt that was returned with a status of "error".

If errors were encountered, they will be present in the `PushErrorCollection`, and the `PushReceiptCollection` will have
a gap in its keys that corresponds to the failed chunk of notifications. Inspect the errors to find out what went wrong.

### Further Information

More detailed information about Expo's Push API can be found on their 
[documentation website](https://docs.expo.dev/push-notifications/sending-notifications/).

## 💬 Support

Please report any problems by submitting an [issue](https://github.com/Dru1X/expo-server-sdk-php/issues). Ensure that 
the problem is well-described and can be replicated by others. All issues will be reviewed as soon as is reasonably 
possible.

## 🤝 Contributing

Thank you for considering contributing! Please open a 
[pull request](https://github.com/Dru1X/expo-server-sdk-php/pulls), ensuring that test coverage is maintained or 
increased with any proposed changes. All pull requests will be reviewed as soon as is reasonably possible.

## 📄 License

Expo Push Server SDK (PHP) is open-sourced software licensed under the [MIT licence](LICENSE.md).