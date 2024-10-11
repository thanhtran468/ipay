<?php

namespace IPay\Builders;

use IPay\Encryption\Encryptor;
use Nette\Utils\Json;
use Nette\Utils\Random;

/**
 * @psalm-type ValueType = string|int
 * @psalm-type ParametersType = array<string, ValueType>
 */
final class BodyBuilder implements \Stringable, \JsonSerializable
{
    /**
     * @param ParametersType $parameters
     */
    public function __construct(
        private array $parameters = [],
    ) {
    }

    public function setSessionId(string $value): void
    {
        $this->parameters['sessionId'] = $value;
    }

    /**
     * @param ParametersType $parameters
     */
    public function build(array $parameters = []): self
    {
        $data = array_merge([
            'lang' => 'en',
            'requestId' => Random::generate(12, '0-9A-Z').'|'.time(),
        ], $this->parameters, $parameters);
        ksort($data);
        $data['signature'] = md5(http_build_query($data));

        return new self($data);
    }

    public function encrypt(): string
    {
        return new self(['encrypted' => Encryptor::encrypt($this)]);
    }

    public function __toString(): string
    {
        return Json::encode($this->parameters);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->parameters;
    }
}
