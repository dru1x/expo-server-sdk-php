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
a `5xx` server error is automatically retried up to 3 times, with a 500ms base delay between
attempts that doubles after each retry (exponential backoff). If all retries are exhausted, the
original exception is thrown.

This behaviour can be customised by supplying a `RetryConfig` to the constructor:

```php
use Dru1x\ExpoPush\ExpoPush;
use Dru1x\ExpoPush\Config\RetryConfig;

$expoPush = new ExpoPush(retryConfig: new RetryConfig(
    tries: 5,
    retryInterval: 1000,
    useExponentialBackoff: false,
));
```

- `tries` — the maximum number of attempts to make for a single request (defaults to `3`).
- `retryInterval` — the base delay between retries, in milliseconds (defaults to `500`).
- `useExponentialBackoff` — whether the delay should double after each attempt (defaults to `true`).
- `throwOnMaxTries` — whether an exception should be thrown once retries are exhausted
  (defaults to `true`); if set to `false`, the last error response is returned instead.

To disable retries entirely, set `tries` to `1`:

```php
$expoPush = new ExpoPush(retryConfig: new RetryConfig(tries: 1));
```

This can also be achieved using the `disabled()` helper:

```php
$expoPush = new ExpoPush(retryConfig: RetryConfig::disabled());
```

Client errors (e.g. `4xx` responses) are never retried, since retrying is unlikely to change the
outcome. If all retries are exhausted, the failure is recorded as a `PushError`.

### Sending Push Notifications

Push notifications can be sent by supplying a `PushMessageList`, or an array of `PushMessage` objects, to the
`sendNotifications()` method. This automatically chunks the given push messages into an appropriate number of requests 
and sends them concurrently to Expo's Push API. The max request size, concurrency limit and rate limit are applied as 
set out in [Expo's Push API documentation](https://docs.expo.dev/push-notifications/sending-notifications/#http2-api). 
Therefore, the maximum throughput of this method is 600 notifications per second.

```php
use Dru1x\ExpoPush\PushError\PushErrorList;
use Dru1x\ExpoPush\PushMessage\PushMessage;
use Dru1x\ExpoPush\PushMessage\PushMessageList;
use Dru1x\ExpoPush\PushTicket\PushTicketMap;
use Dru1x\ExpoPush\PushToken\PushToken;

// This could also be an array of PushMessage objects
$messages = new PushMessageList([
    new PushMessage(
        to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
        title: 'Hello',
        body: 'This is a push notification',
    ),
]);

$result = $expoPush->sendNotifications($messages);

/** @var PushTicketMap $tickets */
$tickets = $result->tickets;

/** @var PushErrorList $errors */
$errors = $result->errors;
```

The `SendNotificationsResult` object returned by `sendNotifications()` contains a map of all the resulting 
`PushTicket` objects, as well as a list of `PushError` objects representing any 
[request-level errors](https://docs.expo.dev/push-notifications/sending-notifications/#request-errors) encountered while 
sending the given batch of notifications.

The `PushTicketMap` is keyed by notification index, following the order of the `PushMessage` objects passed in to 
`sendNotifications()` (a message with multiple recipients produces one notification, and therefore one ticket, per 
recipient). Each `PushTicket` will either be a `SuccessfulPushTicket` or a `FailedPushTicket`, the latter representing a 
ticket that was returned with a status of "error".

```php
foreach ($result->tickets as $index => $ticket) {
    if ($ticket->isSuccessful()) {
        // Store $ticket->receiptId so the receipt can be checked later
    }
}
```

If errors were encountered, they will be present in the `PushErrorList`, and the `PushTicketMap` will have 
a gap in its keys that corresponds to the failed chunk of notifications. Each `PushError` has a `startIndex` and 
`endIndex` identifying the affected notifications. Inspect the errors to find out what went wrong.

### Checking Tickets

Once notifications have been sent, Expo recommends that 
[notification outcomes are checked](https://docs.expo.dev/push-notifications/sending-notifications/#check-push-receipts-for-errors). 
This is done by fetching `PushReceipt` objects, each identified by an ID included in a previously returned `PushTicket`.

Receipts can be fetched by supplying a `PushReceiptIdList`, or an array of push receipt ID strings, to the 
`getReceipts()` method. This also automatically chunks the given receipt IDs into an appropriate number of requests 
and sends them concurrently to the Expo Push API.

Expo recommends that this is done between 15 minutes and 24 hours after notifications were sent.

```php
use Dru1x\ExpoPush\PushError\PushErrorList;
use Dru1x\ExpoPush\PushReceipt\PushReceiptIdList;
use Dru1x\ExpoPush\PushReceipt\PushReceiptMap;

// This could also be an array of receipt ID strings
$receiptIds = new PushReceiptIdList([
    'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
    'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
]);

$result = $expoPush->getReceipts($receiptIds);

/** @var PushReceiptMap $receipts */
$receipts = $result->receipts;

/** @var PushErrorList $errors */
$errors = $result->errors;
```

The `GetReceiptsResult` object returned by `getReceipts()` contains a map of the resulting `PushReceipt` objects,
as well as a list of `PushError` objects representing any 
[request-level errors](https://docs.expo.dev/push-notifications/sending-notifications/#request-errors) encountered while 
getting the given batch of receipts.

The `PushReceiptMap` is keyed by receipt ID, so a specific receipt can be found with the `get()` method, or all receipts 
can be iterated over along with their IDs. Each `PushReceipt` will either be a `SuccessfulPushReceipt` or a 
`FailedPushReceipt`, the latter representing a receipt that was returned with a status of "error".

```php
$receipt = $result->receipts->get('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');

foreach ($result->receipts as $receiptId => $receipt) {
    if ($receipt->isFailed()) {
        // Inspect $receipt->message and $receipt->details
    }
}
```

Expo [omits receipts that are not yet available](https://docs.expo.dev/push-notifications/sending-notifications/#check-push-receipts-for-errors), 
so the map may contain fewer receipts than the number of IDs requested, and `get()` will return `null` for any 
receipt ID that is missing. If a missing ID isn't covered by a `PushError`, its receipt simply isn't ready yet and can 
be requested again later.

If errors were encountered, they will be present in the `PushErrorList`, and the receipts for the failed chunk of 
receipt IDs will be absent from the `PushReceiptMap`. Each `PushError` has a `startIndex` and `endIndex` identifying 
the affected positions in the list of receipt IDs that was passed to `getReceipts()`. Inspect the errors to find out 
what went wrong.

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