<?php

namespace IPay\Api;

use IPay\Encryption\Encrypter;
use Nette\Utils\Json;

/**
 * @extends \ArrayObject<string,string>
 */
final class BodyBuilder extends \ArrayObject implements \Stringable, \JsonSerializable
{
    /**
     * @param string[] $data
     */
    private function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * @param string[] $data
     */
    public static function from(array $data): static
    {
        return new static($data);
    }

    /**
     * @param string[] $parameters
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
