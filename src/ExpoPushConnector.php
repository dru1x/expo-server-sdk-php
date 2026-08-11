<?php

namespace Dru1x\ExpoPush;

use Composer\InstalledVersions;
use Dru1x\ExpoPush\Config\RetryConfig;
use Dru1x\ExpoPush\Support\RetriesRequests;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Request;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\MemoryStore;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;
use Throwable;

class ExpoPushConnector extends Connector
{
    use HasRateLimits;
    use RetriesRequests;

    public const MAX_CONCURRENT_REQUESTS = 6;

    public function __construct(
        protected ?string $authToken = null,
        ?RateLimitStore   $rateLimitStore = null,
        RetryConfig       $retryConfig = new RetryConfig(),
    ) {
        $this->rateLimitStore = $rateLimitStore;

        $this->tries = $retryConfig->tries;
        $this->retryInterval = $retryConfig->retryInterval;
        $this->useExponentialBackoff = $retryConfig->useExponentialBackoff;
        $this->throwOnMaxTries = $retryConfig->throwOnMaxTries;
    }

    public function resolveBaseUrl(): string
    {
        return 'https://exp.host/--/api/v2/push';
    }

    // Sending ----

    /**
     * Send a request asynchronously, retrying in the event of transient failures.
     *
     * @param callable(Throwable, Request): (bool)|null $handleRetry
     * @throws FatalRequestException|RequestException
     */
    public function sendAsync(Request $request, ?MockClient $mockClient = null, ?callable $handleRetry = null): PromiseInterface
    {
        return $this->sendAsyncWithRetries($request, $mockClient, $handleRetry);
    }

    // Rate Limits ----

    protected function resolveLimits(): array
    {
        return [
            Limit::allow(6)
                ->everySeconds(1)
                ->sleep()
                ->setPrefix('expo')
                ->name('push-limit'),
        ];
    }

    protected function resolveRateLimitStore(): RateLimitStore
    {
        return $this->rateLimitStore ?? new MemoryStore();
    }

    // Helpers ----

    /**
     * Get the installed version of this SDK
     *
     * @return string
     */
    public function sdkVersion(): string
    {
        static $version = null;

        if (!$version) {
            $composer = json_decode(
                file_get_contents(dirname(__DIR__) . '/composer.json'),
            );

            $version = InstalledVersions::getPrettyVersion($composer->name);
        }

        return (string) $version;
    }

    // Internals ----

    /** @inheritDoc */
    protected function defaultHeaders(): array
    {
        return [
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent' => "expo-server-sdk-php/{$this->sdkVersion()} (dru1x)",
        ];
    }

    /** @inheritDoc */
    protected function defaultAuth(): ?TokenAuthenticator
    {
        return $this->authToken ? new TokenAuthenticator($this->authToken) : null;
    }
}
