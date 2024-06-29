<?php

namespace IPay\Api;

use IPay\Encryption\Encrypter;
use Nette\Utils\Json;
use Nette\Utils\Random;

/**
 * @extends \ArrayObject<string,string>
 */
final class FieldBuilder extends \ArrayObject implements \Stringable, \JsonSerializable
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
    public static function with(array $data): static
    {
        return new static($data);
    }

    public function withRequiredFields(): self
    {
        $this['lang'] = 'en';
        $this['requestId'] = Random::generate(12, '0-9A-Z').'|'.time();

        return $this;
    }

    public function build(): self
    {
        $this->ksort();

        $this['signature'] = md5(http_build_query($this->getArrayCopy()));

        return $this;
    }

    public function encrypt(): string
    {
        return new static([
            'encrypted' => Encrypter::encrypt($this->build()),
        ]);
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
