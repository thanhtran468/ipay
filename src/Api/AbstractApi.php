<?php

namespace IPay\Api;

use IPay\IPayClient;
use League\ObjectMapper\KeyFormatterWithoutConversion;
use League\ObjectMapper\ObjectMapper;
use League\ObjectMapper\ObjectMapperUsingReflection;
use League\ObjectMapper\ReflectionDefinitionProvider;
use Nette\Utils\Json;

abstract class AbstractApi
{
    private ObjectMapper $objectMapper;

    public function __construct(
        protected IPayClient $client,
    ) {
        $this->objectMapper = new ObjectMapperUsingReflection(
            new ReflectionDefinitionProvider(
                keyFormatter: new KeyFormatterWithoutConversion(),
            ),
        );
    }

    /**
     * @param string[] $data
     *
     * @return mixed[]
     */
    protected function post(string $uri, array $data = []): array
    {
        $response = $this->client->getClient()->post(
            $uri,
            [],
            $this->configureFieldBuilder(
                FieldBuilder::with($data)
            )->encrypt()
        );

        return Json::decode((string) $response->getBody(), true);
    }

    protected function configureFieldBuilder(FieldBuilder $builder): FieldBuilder
    {
        return $builder->withRequiredFields();
    }

    public function getObjectMapper(): ObjectMapper
    {
        return $this->objectMapper;
    }
}
