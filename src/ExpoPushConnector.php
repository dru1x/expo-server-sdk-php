<?php

namespace Dru1x\ExpoPush;

use Composer\InstalledVersions;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\MemoryStore;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;

final class ExpoPushConnector extends Connector
{
    use HasRateLimits;

    public const MAX_CONCURRENT_REQUESTS = 6;

    public function __construct(protected ?string $authToken = null, ?RateLimitStore $rateLimitStore = null)
    {
        $this->rateLimitStore = $rateLimitStore;
    }

    public function resolveBaseUrl(): string
    {
        return 'https://exp.host/--/api/v2/push';
    }

    // Rate Limits ----

    protected function resolveLimits(): array
    {
        return [
            Limit::allow(6)
                ->everySeconds(1)
                ->sleep()
                ->name('expo-push-limit'),
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
        $composer = json_decode(
            file_get_contents(dirname(__DIR__) . '/composer.json')
        );

        return InstalledVersions::getPrettyVersion($composer->name);
    }

    // Internals ----

    /** @inheritDoc */
    protected function defaultHeaders(): array
    {
        return [
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent'      => "expo-server-sdk-php/{$this->sdkVersion()} (dru1x)",
        ];
    }

    /** @inheritDoc */
    protected function defaultAuth(): ?TokenAuthenticator
    {
        return $this->authToken ? new TokenAuthenticator($this->authToken) : null;
    }
}