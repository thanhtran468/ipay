<?php

namespace IPay;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\ContentTypePlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use IPay\Api\UnauthenticatedApi;
use IPay\Http\Plugin\ExceptionThrower;
use IPay\Session\UnauthenticatedSession;

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
}
