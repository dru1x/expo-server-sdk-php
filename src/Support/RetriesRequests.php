<?php

namespace Dru1x\ExpoPush\Support;

use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Request;
use Throwable;

/**
 * This adds a retry capability to asynchronous requests sent through a Saloon connector.
 *
 * @see Connector::send()
 * @see https://github.com/saloonphp/saloon/pull/323
 */
trait RetriesRequests
{
    /**
     * Send a synchronous request and retry if it fails.
     *
     * @param callable(Throwable, Request): (bool)|null $handleRetry
     * @throws FatalRequestException|RequestException
     */
    protected function sendAsyncWithRetries(Request $request, ?MockClient $mockClient = null, ?callable $handleRetry = null, int $attempts = 1): PromiseInterface
    {
        // Allow retries by default, unless `$handleRetry` is specified
        if (is_null($handleRetry)) {
            $handleRetry = static fn(Throwable $throwable, Request $request): bool => true;
        }

        // Send off the request asynchronously and register a rejection handler
        return parent::sendAsync($request, $mockClient)->otherwise(function (FatalRequestException|RequestException $exception) use ($request, $mockClient, $handleRetry, $attempts) {

            $maxTries = $request->tries ?? $this->tries;
            $retryInterval = $request->retryInterval ?? $this->retryInterval;
            $throwOnMaxTries = $request->throwOnMaxTries ?? $this->throwOnMaxTries;
            $useExponentialBackoff = $request->useExponentialBackoff ?? $this->useExponentialBackoff;

            // Ensure that max tries is 1 or greater, and retry interval is 0 or greater
            $maxTries = max($maxTries, 1);
            $retryInterval = max($retryInterval, 0);

            // Extract the included exception response, if possible
            $exceptionResponse = $exception instanceof RequestException ? $exception->getResponse() : null;

            // Handle a totally failed request
            if ($exception instanceof FatalRequestException) {
                $exception->getPendingRequest()->executeFatalPipeline($exception);
            }

            // Handle max tries being exhausted
            if ($attempts === $maxTries) {
                return isset($exceptionResponse) && $throwOnMaxTries === false ? $exceptionResponse : throw $exception;
            }

            // Check if retries are allowed for this request
            $allowRetry = $handleRetry($exception, $request)
                && $request->handleRetry($exception, $request)
                && $this->handleRetry($exception, $request);

            // Handle retries being disallowed
            if ($allowRetry === false) {
                return isset($exceptionResponse) && $throwOnMaxTries === false ? $exceptionResponse : throw $exception;
            }

            // Wait for the appropriate amount of time based on the retry config
            $this->waitBeforeRetry($attempts, $retryInterval, $useExponentialBackoff);

            // Retry the request
            return $this->sendAsyncWithRetries($request, $mockClient, $handleRetry, $attempts + 1);
        });
    }

    /**
     * Define whether the request should be retried.
     */
    public function handleRetry(FatalRequestException|RequestException $exception, Request $request): bool
    {
        // Always retry completely failed requests
        if ($exception instanceof FatalRequestException) {
            return true;
        }

        // Otherwise only retry responses with a 5xx status
        return $exception->getResponse()->status() >= 500;
    }

    /**
     * Wait before the next retry, based on the retry config.
     */
    protected function waitBeforeRetry(int $attempts, int $interval, bool $useExponentialBackoff): void
    {
        // From the 2nd attempt onwards, wait before executing the attempt
        if ($attempts > 0) {
            $sleepTime = $useExponentialBackoff
                ? $interval * (2 ** ($attempts - 2)) * 1000
                : $interval * 1000;

            usleep($sleepTime);
        }
    }
}
