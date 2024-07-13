<?php

namespace IPay\Session;

use Nette\Utils\Random;

class UnauthenticatedSession implements SessionInterface
{
    public function getRequestParameters(): array
    {
        return [
            'lang' => 'en',
            'requestId' => Random::generate(12, '0-9A-Z').'|'.time(),
        ];
    }
}
