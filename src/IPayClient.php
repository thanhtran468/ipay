<?php

namespace IPay;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\ContentTypePlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use IPay\Api\AuthenticatedApi;
use IPay\Api\AuthenticatedSession;
use IPay\Api\UnauthenticatedApi;
use IPay\Api\UnauthenticatedSession;
use IPay\Http\Plugin\ExceptionThrower;

final class IPayClient
{
    public static function create(): static
    {
        return new self(
            new HttpMethodsClient(
                new PluginClient(Psr18ClientDiscovery::find(), [
                    new BaseUriPlugin(Psr17FactoryDiscovery::findUriFactory()
                        ->createUri('https://api-ipay.vietinbank.vn')
                    ),
                    new ContentTypePlugin(),
                    new ExceptionThrower(),
                ]),
                Psr17FactoryDiscovery::findRequestFactory(),
                Psr17FactoryDiscovery::findStreamFactory(),
            ),
        );
    }

    private function __construct(
        private HttpMethodsClientInterface $client,
    ) {
    }

    public function getClient(): HttpMethodsClientInterface
    {
        return $this->client;
    }

    public function guest(): UnauthenticatedApi
    {
        return new UnauthenticatedApi($this, new UnauthenticatedSession());
    }

    public function session(string $id): AuthenticatedApi
    {
        return new AuthenticatedApi($this, new AuthenticatedSession($id));
    }
}
