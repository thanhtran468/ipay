<?php

namespace IPay\Builders;

use IPay\Encryption\Encrypter;
use Nette\Utils\Json;
use Nette\Utils\Random;

/**
 * @psalm-type ValueType = string|int
 * @psalm-type ParametersType = array<string, ValueType>
 */
final class BodyBuilder
{
    private ?string $sessionId = null;

    /**
     * @param ParametersType $parameters
     */
    public function __construct(
        private array $parameters = [],
    ) {
    }

    /**
     * @param ParametersType $parameters
     */
    public function with(array $parameters): self
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    public function withSessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function encrypt(): string
    {
        $data = array_merge($this->parameters, [
            'lang' => 'en',
            'requestId' => Random::generate(12, '0-9A-Z').'|'.time(),
        ]);
        $this->sessionId && $data['sessionId'] = $this->sessionId;
        ksort($data);
        $data['signature'] = md5(http_build_query($data));
        try {
            return Json::encode([
                'encrypted' => Encrypter::encrypt(Json::encode($data)),
            ]);
        } finally {
            $this->parameters = [];
        }
    }
}
