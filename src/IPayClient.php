<?php

namespace IPay;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\ContentTypePlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use IPay\Api\Auth;
use IPay\Http\Plugin\ExceptionThrower;

final class IPayClient
{
    public static function create(): static
    {
        return new self(
            new HttpMethodsClient(
                new PluginClient(Psr18ClientDiscovery::find(), [
                    new BaseUriPlugin(Psr17FactoryDiscovery::findUriFactory()
                        ->createUri('https://api-ipay.vietinbank.vn/ipay/wa')
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

    public function auth(): Auth
    {
        return new Auth($this);
    }
}
