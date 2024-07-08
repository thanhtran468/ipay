<?php

namespace IPay\Api;

use EventSauce\ObjectHydrator\DefinitionProvider;
use EventSauce\ObjectHydrator\KeyFormatterWithoutConversion;
use EventSauce\ObjectHydrator\ObjectMapper;
use EventSauce\ObjectHydrator\ObjectMapperUsingReflection;
use IPay\IPayClient;
use Nette\Utils\Json;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template T of SessionInterface
 */
abstract class AbstractApi
{
    protected ObjectMapper $objectMapper;

    /**
     * @param T $session
     */
    public function __construct(
        protected IPayClient $iPayClient,
        private SessionInterface $session,
    ) {
        $this->objectMapper = new ObjectMapperUsingReflection(
            new DefinitionProvider(
                keyFormatter: new KeyFormatterWithoutConversion(),
            ),
        );
    }

    /**
     * @param string[] $parameters
     *
     * @return mixed[]
     */
    protected function post(string $uri, array $parameters = []): array
    {
        $response = $this->iPayClient->getClient()->post(
            sprintf('ipay/wa/%s', $uri),
            [],
            BodyBuilder::from($parameters)
                ->enhance($this->getSession()->getRequestParameters())
                ->build()
                ->encrypt()
        );

        return Json::decode((string) $response->getBody(), true);
    }

    protected static function createOptionsResolver(): OptionsResolver
    {
        return new OptionsResolver();
    }

    /**
     * @return T
     */
    public function getSession(): SessionInterface
    {
        return $this->session;
    }
}
