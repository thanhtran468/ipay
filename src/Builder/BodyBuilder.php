<?php

namespace IPay\Builder;

use IPay\Encryption\Encrypter;
use Nette\Utils\Json;

/**
 * @extends \ArrayObject<string,string>
 *
 * @psalm-type ParametersType = string[]
 */
final class BodyBuilder extends \ArrayObject implements \Stringable, \JsonSerializable
{
    /**
     * @param ParametersType $array
     */
    private function __construct(array $array)
    {
        parent::__construct($array);
    }

    /**
     * @param ParametersType $parameters
     */
    public static function from(array $parameters): static
    {
        return new static($parameters);
    }

    /**
     * @param ParametersType $parameters
     */
    public function enhance(array $parameters): static
    {
        return static::from($this->getArrayCopy() + $parameters);
    }

    public function build(): self
    {
        $this->ksort();

        $this['signature'] = md5(http_build_query($this->getArrayCopy()));

        return $this;
    }

    public function encrypt(): string
    {
        return static::from(['encrypted' => Encrypter::encrypt($this)]);
    }

    public function __toString(): string
    {
        return Json::encode($this);
    }

    /**
     * @return string[]
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return $this->getArrayCopy();
    }
}
