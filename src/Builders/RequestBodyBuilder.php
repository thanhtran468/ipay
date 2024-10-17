<?php

namespace IPay\Builders;

use IPay\Encryption\Encryptor;
use Nette\Utils\Json;

/**
 * @psalm-type ValueType = string|int
 * @psalm-type ParametersType = array<string, ValueType>
 */
final class RequestBodyBuilder implements \Stringable, \JsonSerializable
{
    /**
     * @param ParametersType $parameters
     */
    private function __construct(
        private array $parameters = [],
    ) {
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param ParametersType $parameters
     */
    public function enhance(array $parameters): self
    {
        return new self($parameters);
    }

    /**
     * @param ParametersType $parameters
     */
    public function build(array $parameters = []): self
    {
        $data = array_merge($this->parameters, $parameters);
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
