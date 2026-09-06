<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\Support;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
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
     * @param int $attempt The index of this request in a sequence of retries
     * @param int $delayMs An optional number of milliseconds to delay this attempt by, without blocking other pooled requests
     *
     * @throws FatalRequestException|RequestException
     */
    protected function sendAsyncWithRetries(Request $request, ?MockClient $mockClient = null, ?callable $handleRetry = null, int $attempt = 1, int $delayMs = 0): PromiseInterface
    {
        // Allow retries by default, unless `$handleRetry` is specified
        if (is_null($handleRetry)) {
            $handleRetry = static fn(Throwable $throwable, Request $request): bool => true;
        }

        // Send off the request asynchronously (deferring it if this is a retry) and register a rejection handler
        return $this->sendAsyncDelayed($request, $mockClient, $delayMs)
            ->otherwise(function (FatalRequestException|RequestException $exception) use ($request, $mockClient, $handleRetry, $attempt) {

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
                if ($attempt === $maxTries) {
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

                // Work out how long to delay the next attempt by, based on the retry config
                $delayMs = $this->calculateRetryDelay($attempt, $retryInterval, $useExponentialBackoff);

                // Retry the request
                return $this->sendAsyncWithRetries($request, $mockClient, $handleRetry, $attempt + 1, $delayMs);
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
     * Send a request asynchronously, deferring its dispatch by the given delay.
     *
     * Unlike `usleep()`, this doesn't block the process - it sets Guzzle's `delay` request
     * option, which is honoured by the underlying handler without stalling other requests
     * that are in flight within the same pool.
     *
     * @see \GuzzleHttp\RequestOptions::DELAY
     */
    protected function sendAsyncDelayed(Request $request, ?MockClient $mockClient = null, int $delayMs = 0): PromiseInterface
    {
        $sender = $this->sender();

        return Utils::task(function () use ($request, $mockClient, $sender, $delayMs) {
            $pendingRequest = $this->createPendingRequest($request, $mockClient)->setAsynchronous(true);

            if ($delayMs > 0) {
                $pendingRequest->config()->add('delay', $delayMs);
            }

            $requestPromise = $pendingRequest->hasFakeResponse()
                ? $this->createFakeResponse($pendingRequest)
                : $sender->sendAsync($pendingRequest);

            return $requestPromise->then(fn($response) => $pendingRequest->executeResponsePipeline($response));
        });
    }

    /**
     * Calculate how long to delay the next retry attempt by, based on the retry config.
     *
     * @param int $attempt The index of the attempt that has just taken place
     * @param int $interval The number of milliseconds to use as the base delay
     * @param bool $useExponentialBackoff If enabled, the interval will be doubled for each subsequent attempt
     * @return int The delay, in milliseconds
     */
    protected function calculateRetryDelay(int $attempt, int $interval, bool $useExponentialBackoff): int
    {
        return $useExponentialBackoff
            ? $interval * (2 ** ($attempt - 1))
            : $interval;
    }
}
