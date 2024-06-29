<?php

namespace IPay\Api;

final class Auth extends AbstractApi
{
    public function login(
        string $userName,
        string $accessCode,
        string $captchaCode,
        string $captchaId,
    ): Session {
        $result = $this->post('/signIn', get_defined_vars());

        return new Session($result['sessionId'], $this->client);
    }
}
